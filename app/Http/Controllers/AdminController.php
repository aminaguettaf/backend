<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller{
    protected  $firestore;
    // constructeur 
    public function __construct() {
        $credentials = config('app.firebase'); // Récupérer la config Firebase
    
        $factory = (new Factory)
            ->withServiceAccount($credentials);
    
        $this->firestore = $factory->createFirestore()->database();
    }
     
    public function login(Request $request){
    try {
        $email = $request->email;
        $password = $request->password;

        // Vérifier si c'est le super admin (.env)
        if ($email === env('ADMIN_EMAIL') && $password === env('ADMIN_PASSWORD')) {
            $payload = [
                'email' => $email,
                'exp' => time() + 3600, // Expire en 1 heure
            ];

            $token = JWT::encode($payload, env('JWT_SECRET'), 'HS256');
            return response()->json([
                'success' => true,
                'token' => $token,
                'message' => 'You have logged in'
            ]);
        }

        //  Accéder à Firestore via ton instance personnalisée
        $adminsRef = $this->firestore->collection('admins')->where('email', '=', $email);
        $documents = $adminsRef->documents();

        if ($documents->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Admin not found'
            ], 404);
        }

        foreach ($documents as $document) {
            $admin = $document->data();

            // Vérifier le mot de passe hashé
            if (Hash::check($password, $admin['password'])) {
                $payload = [
                    'email' => $email,
                    'role' => $admin['role'],
                    'exp' => time() + 3600, // 1 heure
                ];

                $token = JWT::encode($payload, env('JWT_SECRET'), 'HS256');
                return response()->json([
                    'success' => true,
                    'token' => $token,
                    'message' => 'You have logged in'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid password'
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'An error occurred'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Une erreur est survenue : ' . $e->getMessage(),
        ], 500);
    }
    }

    public function createAdmin(Request $request){
        try {
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ], [
                'email.required' => 'Email is required',
                'password.min' => 'Password must be at least 6 characters',
            ]);
            // Vérifier si l'email existe déjà dans Firestore
            $adminsRef = $this->firestore->collection('admins')->where('email', '=', $request->email);
            $documents = $adminsRef->documents();
            if (!$documents->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email already exists'
                ]);
            }
            $hashedPassword = Hash::make($request->password);

            // Ajouter l'admin à Firestore
            $this->firestore->collection('admins')->add([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $hashedPassword,
                'role' => 'admin', 
                'created_at' => now()->toDateTimeString(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Admin created successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }
    public function getAdmins() {
        try {
            $adminsRef = $this->firestore->collection('admins');
            $documents = $adminsRef->documents();

            $admins = [];
            foreach ($documents as $document) {
                $adminData = $document->data();
                $adminData['id'] = $document->id(); // Ajouter l'ID Firestore
                unset($adminData['password']); // Ne pas envoyer le mot de passe
                $admins[] = $adminData;
             }

            return response()->json([
                'success' => true,
                'admins' => $admins
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }


}
