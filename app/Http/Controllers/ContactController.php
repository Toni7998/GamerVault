<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\ContactMessage;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    // Método para mostrar el formulario de contacto
    public function create()
    {
        return view('pages.contacte');
    }

    // Método para manejar el envío del formulario de contacto
    public function store(Request $request)
    {
        // Validación del formulario
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:1000',
        ]);

        // Enviar el correo
        Mail::to('antonio.ruiz@insbaixcamp.cat')->send(new ContactMessage(
            $validatedData['name'],
            $validatedData['email'],
            $validatedData['message']
        ));

        // Redirigir con mensaje de éxito
        return back()->with('success', 'El teu missatge ha estat enviat amb èxit!');
    }
}
