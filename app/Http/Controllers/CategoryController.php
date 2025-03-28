<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;

class CategoryController extends Controller{
    protected  $firestore;
    public function __construct(){
        // $firebaseCredentials = json_decode(env('FIREBASE_CREDENTIALS'), true);
        // $factory = (new Factory)->withServiceAccount($firebaseCredentials);
        $factory = (new Factory) ->withServiceAccount(__DIR__.'/firebase_credentials.json');

        $this->firestore = $factory->createFirestore()->database();
    }

    // Ajouter une categorie
    public function addCategory(Request $request){
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'image' => 'nullable|string',             
            ]);
            $newCategory = $this->firestore->collection('categories')->add([
                'name' => $request->name,
                'image' => $request->image,
                'created_at' => now()->toDateTimeString(),
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Category added successfully',
                'id' => $newCategory->id(),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // récupérer toutes les catégories
    public function getAllCategories(){
        try {
            $categoriesQuery = $this->firestore->collection('categories')->documents();
            $categories = [];
            foreach($categoriesQuery as $category){
                if($category->exists()){
                    $categories[] = array_merge(['id' => $category->id()], $category->data());
                }
            }
            if(empty($categories)){
                return response()->json([
                    'success' => false,
                    'message' => 'No categories found',
                ], 404);
            }
            return response()->json([
                'success' => true,
                'categories' => $categories,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Mettre à jour une catégorie
    public function updateCategory(Request $request, $id){
        try {
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'image' => 'sometimes|string',
            ]);
            
            $categoryRef = $this->firestore->collection('categories')->document($id);
            $categorySnapshot = $categoryRef->snapshot();

            if (!$categorySnapshot->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found',
                ], 404);
            }
            
            $categoryRef->set([
                'name' => $request->name,
                'image' => $request->image,
                'created_at' => now()->toDateTimeString(),
            ], ['merge' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'id' => $id
            ], 200);
        
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Supprimer une catégorie
    public function deleteCategory($id){
        try {
            $categoryRef = $this->firestore->collection('categories')->document($id);
            $categorySnapshot = $categoryRef->snapshot();

            if (!$categorySnapshot->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            $categoryRef->delete();
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
