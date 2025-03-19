<?php

namespace App\Http\Controllers;

// importation de la classe request pour récupérer les données envoyées par le client
use Illuminate\Http\Request;
// importation de la classe Factory pour interagir avec firebase
use Kreait\Firebase\Factory;

class ProductController extends Controller{

    // initialisation de firestore
    // stocker la connexion à firestore
    protected  $firestore;
    // constructeur 
    public function __construct(){
    // initialise factory avec les clé qui se trouve dans firebase_credentials.json
        $factory = (new Factory)
        ->withServiceAccount(__DIR__.'/firebase_credentials.json');
    // créer une instance de firestore et stocke la connexion dans $this->firestore
        $this->firestore = $factory->createFirestore()->database();
    }

    // *****************************************
    // Ajouter un produit
    // prend un objet en parametre, qui contient les dnnées envoyées par un client (via un formulaire)
    public function store(Request $request) {
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
        // Retourne une réponse JSON avec un message de succès et l’ID du produit ajouté
        return response()->json([
            'success' => true,
            'message' => 'Product added successfully',
            'id' => $newProduct->id(),
        ]);
    }

    // *****************************************
    // Récuperer et afficher tous les produits 
    public function index(){
        try {
            // recuperer tous les documents de la collection products
            $productsQuery = $this->firestore->collection('products')->documents();
            // creer un tableau products pour les stocker
            $products = [];
            // parcours chaque document et l ajoute au tableau products s'il existe
            foreach ($productsQuery as $product) {
                if ($product->exists()) {
                    $products[] = array_merge(['id' => $product->id()], $product->data());
                }
            }
            // si aucun le tableau products est vide retourne une reponse json 
            if (empty($products)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No products found',
                ], 404);
            }
            // sinon retourné les produits trouvés
            return response()->json([
                'success' => true,
                'products' => $products,
            ], 200);
        // en cas d'erreur retourne un msg d'erreur 
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
 
    // *****************************************
    // mettre a jour un produit
    public function update(Request $request, $id) {
        // valider les données
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
    
        // récuperer le produit par son id
        $productRef = $this->firestore->collection('products')->document($id);
        // snapshot pour recuperer un copie du document en lecture pour vérifier si le produit existe 
        $productSnapshot = $productRef->snapshot();
        if (!$productSnapshot->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
    
        // Met a jour le produit avec les nouvelles valeurs envoyés
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
        ], ['merge' => true]); // `merge` permet de ne pas écraser les autres champs
    
        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'id' => $id
        ]);
    }

    // supprimer un produit 
    public function delete($id){
        try {
           // Récupérer le produit par son id
           $productRef = $this->firestore->collection('products')->document($id);
           $productSnapshot = $productRef->snapshot();

            // Vérifier si le produit existe
            if (!$productSnapshot->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

           // Supprimer le document
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
