<?php

namespace App\Http\Controllers;

// importation de la classe request pour récupérer les données envoyées par le client
use Illuminate\Http\Request;
// importation de la classe Factory pour interagir avec firebase
use Kreait\Firebase\Factory;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller{

    // initialisation de firestore
    // stocker la connexion à firestore
    protected  $firestore;
    // constructeur 
    public function __construct() {
        $credentials = config('app.firebase'); // Récupérer la config Firebase
    
        $factory = (new Factory)
            ->withServiceAccount($credentials);
    
        $this->firestore = $factory->createFirestore()->database();
    }

    // *****************************************
    // Ajouter un produit
    public function store(Request $request) {
        try {
            // vérifier si les données envoyées respectent les regles
            $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'size' => 'nullable|array',
                'color' => 'nullable|array',
                'available' => 'required|boolean',
                'image' => 'nullable|string',
                'category' => 'required|string|max:255',
            ],[
                'name.required' => 'The name field is required.',
                'price.required' => 'The price field is required.',
                'price.numeric' => 'The price must be a number.',
                'price.min' => 'The price must be at least 0.',
                'available.required' => 'The available field is required.',
                'category.required' => 'The category field is required.',
            ]);
    
            // ajoute un nouveau document à la collection products avec les données reçues.
            $newProduct = $this->firestore->collection('products')->add([
                'name' => $request->name,
                'price' => $request->price,
                'description' => $request->description,
                'size' => $request->size,
                'color' => $request->color,
                'available' => $request->available,
                'image' => $request->image,
                'category' => $request->category,
                'created_at' => now()->toDateTimeString(),
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Product added successfully',
                'id' => $newProduct->id(),
            ]);
        }
         catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // *****************************************
    // Récuperer et afficher tous les produits 
    public function index(){
        try {
            $productsQuery = $this->firestore->collection('products')->documents();
            $products = [];
            foreach ($productsQuery as $product) {
                if ($product->exists()) {
                    $products[] = array_merge(['id' => $product->id()], $product->data());
                }
            }
            if (empty($products)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No products found',
                ], 404);
            }
            return response()->json([
                'success' => true,
                'products' => $products,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
 
    // *****************************************
    // Mettre à jour un produit
    public function update(Request $request, $id) {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'size' => 'nullable|array',
                'color' => 'nullable|array',
                'available' => 'required|boolean',
                'image' => 'nullable|string',
                'category' => 'required|string|max:255',
            ]);
    
            $productRef = $this->firestore->collection('products')->document($id);
            $productSnapshot = $productRef->snapshot();
            if (!$productSnapshot->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }
    
            $productRef->set([
                'name' => $request->name,
                'price' => $request->price,
                'description' => $request->description,
                'size' => $request->size,
                'color' => $request->color,
                'available' => $request->available,
                'image' => $request->image,
                'category' => $request->category,
                'updated_at' => now()->toDateTimeString(),
            ], ['merge' => true]);
    
            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'id' => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // *****************************************
    // Supprimer un produit
    public function delete($id){
        try {
            $productRef = $this->firestore->collection('products')->document($id);
            $productSnapshot = $productRef->snapshot();

            if (!$productSnapshot->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $productRef->delete();
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
