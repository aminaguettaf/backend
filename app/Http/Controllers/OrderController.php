<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;

class OrderController extends Controller{
    protected  $firestore;
    public function __construct(){
        $factory = (new Factory)
        ->withServiceAccount(__DIR__.'/firebase_credentials.json');

        $this->firestore = $factory->createFirestore()->database();
    }

    // récuperer toutes les commandes
    public function getAllOrders(){
        try {
            $ordersQuery = $this->firestore->collection('orders')->documents();
            $orders = [];

            foreach($ordersQuery as $order){
                if($order->exists()){
                    $orders[] = array_merge(['id' => $order->id()], $order->data());
                }
            }
            if(empty($orders)){
                return response()->json([
                    'success' => false,
                    'message' => 'No order found'

                ], 404);
            }
            return response()->json([
                'success' => true,
                'orders' => $orders,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // mettre a jour une commande 
    public function updateOrder(Request $request, $id) {
        try {
            $orderRef = $this->firestore->collection('orders')->document($id);
            $order = $orderRef->snapshot();
    
            if (!$order->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }
    
            if (!$request->has('orderStatus')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No order status provided'
                ], 400);
            }
    
            // Convertir en string pour éviter les erreurs Firestore
            $orderStatus = $request->input('orderStatus');
    
            try {
                $orderRef->update([
                    ['path' => 'orderStatus', 'value' => $orderStatus]
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Firestore update failed',
                    'error' => $e->getMessage()
                ], 500);
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'updatedStatus' => $orderStatus
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    
    // supprimer une commande 
    public function deleteOrder($id) {
        try {
            $orderRef = $this->firestore->collection('orders')->document($id);
            $order = $orderRef->snapshot();
    
            if (!$order->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }
    
            // Suppression du document Firestore
            $orderRef->delete();
    
            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
}
