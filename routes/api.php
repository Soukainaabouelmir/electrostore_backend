<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ListeProduits\Categories\CategoriesController;
use App\Http\Controllers\ListeProduits\Marque\MarqueController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

Route::post('/login', function (Request $request) {

       \Log::info('Tentative de login API', ['email' => $request->email]);
    try {
        // Validation
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Tentative d'authentification
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Email ou mot de passe incorrect',
                'code' => 'INVALID_CREDENTIALS'
            ], 401);
        }

        $user = Auth::user();

        // Création du token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ]);
    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'Erreur de validation',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        Log::error('Erreur lors du login', ['exception' => $e]);

        return response()->json([
            'message' => 'Erreur serveur interne',
            'error' => $e->getMessage(),
        ], 500);
    }
});

Route::post('/sign-up',[RegisteredUserController::class ,'store']);












//////////////////////////////////////////////////////////////////////////////////////////////////////////////////Liste des produits ADMIN SECTION///////////////////////////////////////////////////////

//////////////////////////////////////////////Categories//////////////////////////////////////////////////
Route::get('/admin/categories',[CategoriesController::class ,'getCategory']);
Route::post('/admin/categories/store',[CategoriesController::class ,'store']);

//////////////////////////////////////////////Marques/////////////////////////////////////////////////////
Route::get('/admin/marques',[MarqueController::class , 'getMarque']);
Route::post('/admin/marques/store',[MarqueController::class , 'store']);















