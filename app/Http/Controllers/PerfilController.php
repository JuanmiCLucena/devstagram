<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;

class PerfilController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('perfil.index');
    }

    public function store(Request $request)
    {

        // Modificar el Request
        $request->request->add(['username' => Str::slug($request->username)]);

        $this->validate($request, [
            'username' => ['required', Rule::unique('users', 'username')->ignore(auth()->user()),  'min:3', 'max:20', 'not_in:twitter,editar-perfil'],
            'email' => ['required', Rule::unique('users', 'email')->ignore(auth()->user())],

        ]);

        if ($request->imagen) {
            $imagen = $request->file('imagen');

            $nombreImagen = Str::uuid() . '.' . $imagen->extension();

            $imagenServidor = Image::make($imagen);
            $imagenServidor->fit(1000, 1000);

            $imagenPath = public_path('perfiles') . '/' . $nombreImagen;
            $imagenServidor->save($imagenPath);
        }

        // Guardar cambios
        $usuario = User::find(auth()->user()->id);
        $usuario->username = $request->username;
        $usuario->email = $request->email ?? auth()->user()->email;
        $usuario->imagen = $nombreImagen ?? auth()->user()->imagen ?? null;

        if ($request->oldpassword || $request->password) {
            $this->validate($request, [
                'oldpassword' => 'required',
                'password' => 'required|confirmed'
            ]);

            if (!Hash::check($request->oldpassword, auth()->user()->password)) {

                return back()->with('mensaje', 'La Contraseña Actual no Coincide');
            }
        }
        
        $usuario->password = Hash::make($request->password) ?? auth()->user()->password;
        $usuario->save();

        // Redireccionar al muro
        return redirect()->route('posts.index', $usuario->username);
    }
}
