<?php

namespace App\Http\Controllers;
use Kreait\Firebase\Factory;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    protected $firestore;
    // Constructeur pour initialiser Firestore
    public function __construct(){
        // $factory = (new Factory) ->withServiceAccount(__DIR__.'/firebase_credentials.json');

        // $this->firestore = $factory->createFirestore()->database();
       
        $credentials = config('app.firebase'); // Récupérer la config Firebase
        
        $factory = (new Factory)
            ->withServiceAccount($credentials);
        
        $this->firestore = $factory->createFirestore()->database();
        
    }

    // methode pour sauvegarder une image
    public function uploadImage(Request $request) {
        // valider l image: obligatoire et doit être un fichier valide (jpeg, png, jpg, gif)
        $request->validate([
            'image' => 'required|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        // vérifier si un fichier a bien été envoyé
        if ($request->hasFile('image')) {
            // récupérer le fichier 
            $image = $request->file('image');
            // générer un nom unique pur chaque image
            $imageName = time().'.'.$image->getClientOriginalExtension();
            // stocker dans storage/app/public/images
            $path = $image->storeAs('images', $imageName, 'public'); 

            // retourne l'URL de l'image stockée
            return response()->json(['image_url' => asset('storage/'.$path)]);
        }
    
        return response()->json(['error' => 'No image uploaded'], 400);
    }
    
}
