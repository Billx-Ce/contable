<?php

namespace App\Http\Controllers\Persona;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Models\Departamento;
use App\Models\Provincia;
use App\Models\Distrito;
use App\Models\Discapacidad;
use Illuminate\Http\Request;
use Inertia\Inertia;

// ➕ AÑADIR:
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PersonaController extends Controller
{
    public function index(Request $request)
    {
        $query = Persona::query()
            ->with([
                'user:id,name,email,persona_id',
                'distrito.provincia.departamento',
                'discapacidad',
            ]);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nombre', 'like', "%{$s}%")
                    ->orWhere('pri_ape', 'like', "%{$s}%")
                    ->orWhere('seg_ape', 'like', "%{$s}%")
                    ->orWhere('num_doc', 'like', "%{$s}%");
            });
        }

        if ($request->filled('tipo_doc')) {
            $query->where('tipo_doc', $request->tipo_doc);
        }

        if ($request->filled('ubigeo_id')) {
            $query->where('ubigeo_com', $request->ubigeo_id);
        }

        $personas = $query->latest()->paginate(10)->withQueryString();

        $personas->getCollection()->transform(function ($p) {
            $p->ubigeo = $p->distrito ? [
                'departamento' => $p->distrito->provincia->departamento->nombre ?? 'N/A',
                'provincia'    => $p->distrito->provincia->nombre ?? 'N/A',
                'distrito'     => $p->distrito->nombre ?? 'N/A',
            ] : null;

            $p->usuario = $p->user ? [
                'id'    => $p->user->id,
                'name'  => $p->user->name,
                'email' => $p->user->email,
            ] : null;

            unset($p->user);
            return $p;
        });

        $ubigeos = Distrito::with('provincia.departamento')
            ->orderBy('nombre')
            ->get()
            ->map(fn ($d) => [
                'codigo'       => $d->ubigeo_com,
                'departamento' => $d->provincia->departamento->nombre ?? 'N/A',
                'provincia'    => $d->provincia->nombre ?? 'N/A',
                'distrito'     => $d->nombre,
            ]);

        return Inertia::render('Persona/Index', [
            'personas' => $personas,
            'filters'  => $request->only(['search', 'tipo_doc', 'ubigeo_id']),
            'ubigeos'  => $ubigeos,
        ]);
    }

    public function create()
    {
        return Inertia::render('Persona/Create', [
            'tiposDocumento' => [
                ['value' => 'DNI', 'text' => 'DNI'],
                ['value' => 'CE', 'text' => 'Carné de Extranjería'],
                ['value' => 'Pasaporte', 'text' => 'Pasaporte'],
            ],
            'departamentos' => Departamento::orderBy('nombre')->get(['ubigeo_dep', 'nombre as text']),
            'provincias'    => [],
            'distritos'     => [],
            'sexos'         => [
                ['value' => 'M', 'text' => 'Masculino'],
                ['value' => 'F', 'text' => 'Femenino'],
            ],
            'discapacidads' => Discapacidad::orderBy('id')
                ->get(['id', 'nombre_discapacidad as text']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'     => 'required|string|max:255',
            'pri_ape'    => 'required|string|max:255',
            'seg_ape'    => 'required|string|max:255',
            'tipo_doc'   => 'required|in:DNI,CE,Pasaporte',
            'num_doc'    => [
                'required', 'string', 'max:20', 'unique:personas',
                function ($attr, $val, $fail) use ($request) {
                    if ($request->tipo_doc === 'DNI' && !preg_match('/^[0-9]{8}$/', $val)) {
                        $fail('El DNI debe tener exactamente 8 dígitos.');
                    }
                    if ($request->tipo_doc === 'CE' && !preg_match('/^[0-9]{9,12}$/', $val)) {
                        $fail('El CE debe tener entre 9 y 12 dígitos.');
                    }
                },
            ],
            'ubigeo_com'      => 'required|exists:distritos,ubigeo_com',
            'sexo'            => 'required|in:M,F',
            'discapacidad_id' => 'nullable|exists:discapacidads,id',
            'fecha_nac'       => 'required|date|before_or_equal:today',
            'direccion'       => 'nullable|string|max:255',
            'telefono'        => 'nullable|string|max:9',
        ]);

        // 1) Crear PERSONA
        $persona = Persona::create($validated);

        // 2) ➕ Crear USUARIO automático si hay DNI
        if ($persona->tipo_doc === 'DNI' && !empty($persona->num_doc)) {
            $base = "{$persona->num_doc}@gmail.com";
            $email = $base;
            $i = 1;
            // Evitar choque si ya existe ese email
            while (User::where('email', $email)->exists()) {
                $email = "{$persona->num_doc}+{$i}@gmail.com";
                $i++;
            }

            $nombreCompleto = trim("{$persona->nombre} {$persona->pri_ape} {$persona->seg_ape}");

            $persona->user()->create([
                'name'     => $nombreCompleto ?: $persona->num_doc,
                'email'    => $email,
                'password' => Hash::make($persona->num_doc), // contraseña = DNI
            ]);
        }

        return redirect()->route('personas.index')
            ->with('success', 'Persona registrada exitosamente');
    }

    public function show(Persona $persona)
    {
        $persona->load([
            'user:id,name,email,persona_id',
            'distrito.provincia.departamento',
            'discapacidad',
        ]);

        return Inertia::render('Persona/Show', [
            'persona'           => $persona,
            'ubicacionCompleta' => $persona->ubicacion_completa,
            'nombreCompleto'    => $persona->nombre_completo,
            'usuario'           => $persona->user,
        ]);
    }

    public function edit(Persona $persona)
    {
        $persona->load('distrito.provincia.departamento');

        $initialData = null;
        if ($persona->distrito) {
            $initialData = [
                'ubigeo_dep' => $persona->distrito->provincia->departamento->ubigeo_dep,
                'ubigeo_pro' => $persona->distrito->provincia->ubigeo_pro,
                'ubigeo_com' => $persona->distrito->ubigeo_com,
            ];
        }

        return Inertia::render('Persona/Edit', [
            'persona'     => $persona,
            'initialData' => $initialData,
            'options'     => [
                'tiposDocumento' => [
                    ['value' => 'DNI', 'text' => 'DNI'],
                    ['value' => 'CE', 'text' => 'Carné de Extranjería'],
                    ['value' => 'Pasaporte', 'text' => 'Pasaporte'],
                ],
                'departamentos' => Departamento::orderBy('nombre')->get(['ubigeo_dep', 'nombre as text']),
                'sexos' => [
                    ['value' => 'M', 'text' => 'Masculino'],
                    ['value' => 'F', 'text' => 'Femenino'],
                ],
                'discapacidads' => Discapacidad::orderBy('id')
                    ->get(['id', 'nombre_discapacidad as text']),
            ],
        ]);
    }

    public function update(Request $request, Persona $persona)
    {
        $validated = $request->validate([
            'nombre'     => 'required|string|max:255',
            'pri_ape'    => 'required|string|max:255',
            'seg_ape'    => 'required|string|max:255',
            'tipo_doc'   => 'required|in:DNI,CE,Pasaporte',
            'num_doc'    => [
                'required', 'string', 'max:20', 'unique:personas,num_doc,' . $persona->id,
                function ($attr, $val, $fail) use ($request) {
                    if ($request->tipo_doc === 'DNI' && !preg_match('/^[0-9]{8}$/', $val)) {
                        $fail('El DNI debe tener exactamente 8 dígitos.');
                    }
                    if ($request->tipo_doc === 'CE' && !preg_match('/^[0-9]{9,12}$/', $val)) {
                        $fail('El CE debe tener entre 9 y 12 dígitos.');
                    }
                },
            ],
            'ubigeo_com'      => 'required|exists:distritos,ubigeo_com',
            'sexo'            => 'required|in:M,F',
            'discapacidad_id' => 'nullable|exists:discapacidads,id',
            'fecha_nac'       => 'required|date|before_or_equal:today',
            'direccion'       => 'nullable|string|max:255',
            'telefono'        => 'nullable|string|max:20',
        ]);

        $persona->update($validated);

        return redirect()->route('personas.index')
            ->with('success', 'Persona actualizada exitosamente');
    }

    public function destroy(Persona $persona)
    {
        $persona->delete();

        return redirect()->route('personas.index')
            ->with('success', 'Persona eliminada exitosamente');
    }

    public function getProvincias($ubigeoDep)
    {
        return response()->json(
            Provincia::where('ubigeo_dep', $ubigeoDep)
                ->orderBy('nombre')
                ->get(['ubigeo_pro', 'nombre as text'])
        );
    }

    public function getDistritos($ubigeoPro)
    {
        return response()->json(
            Distrito::where('ubigeo_pro', $ubigeoPro)
                ->orderBy('nombre')
                ->get(['ubigeo_com', 'nombre as text'])
        );
    }

    public function getUbigeoData()
    {
        return response()->json([
            'departamentos' => Departamento::orderBy('nombre')->get(['ubigeo_dep as value', 'nombre as text']),
            'provincias'    => Provincia::orderBy('nombre')->get(['ubigeo_pro as value', 'ubigeo_dep', 'nombre as text']),
            'distritos'     => Distrito::orderBy('nombre')->get(['ubigeo_com as value', 'ubigeo_pro', 'nombre as text']),
        ]);
    }

    public function confirmDelete(Persona $persona)
    {
        return Inertia::render('Persona/ConfirmDelete', ['persona' => $persona]);
    }

    public function usuarios(Persona $persona)
    {
        $persona->load('user:id,name,email,persona_id');

        return Inertia::render('Persona/Usuarios', [
            'persona' => [
                'id'      => $persona->id,
                'nombre'  => $persona->nombre,
                'pri_ape' => $persona->pri_ape,
                'seg_ape' => $persona->seg_ape,
            ],
            'usuario' => $persona->user,
        ]);
    }
}
