<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\AuthCollection;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function index()
    {
        $users=User::with(['reservations', 'winners'])->get();
        return new AuthCollection($users);
    }

    public function login(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'identifiant' => 'required', // Peut être l'email ou le numéro de téléphone
            'password' => 'required'
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validatedData->errors()
            ], 400);
        }

        $field = filter_var($request->identifiant, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $user = User::where($field, $request->identifiant)->first();

        if (!$user) {
            return response()->json([
                'status' => 400,
                'message' => "Vous n'avez pas encore un compte"
            ], 400);
        }


        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 400,
                'message' => 'Mot de passe incorrect'
            ], 400);
        }


        $token = $user->createToken("API TOKEN")->plainTextToken;

        return response()->json([
            'status' => 200,
            'message' => 'Connexion réussie',
            'token' => $token,
            'user' => $user
        ], 200);
    }

    public function show(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'user' => $user
        ], 200);
    }


    public function register(Request $request)
    {
        // Validation des données
        $validatedData = Validator::make($request->all(), [
            'last_name' => 'required',
            'first_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'gender' => 'required',
            'isAdmin' => 'sometimes|boolean',
            'profession' => 'required',
            'phone' => 'required|unique:users',
            'date_of_birth' => 'required',
            'nationality' => 'required',
            'current_city' => 'required',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validatedData->fails()) {
            $errors = $validatedData->errors();
            $message = 'Inscription échouée. ';

            if ($errors->has('email')) {
                $message .= 'Cet email est déjà utilisé. ';
            }

            if ($errors->has('phone')) {
                $message .= 'Ce numéro de téléphone est déjà utilisé. ';
            }


            return response()->json([
                'status' => 400,
                'message' => $message,
                'errors' => $errors
            ], 400);
        }

        // Gestion de la photo de profil
        $profilePhotoPath = null;
        // if ($request->hasFile('profile_photo')) {
        //     $profilePhotoPath = $request->file('profile_photo')->store('profile_photos', 'public');
        // }

        // Création de l'utilisateur
        $user = User::create([
            'last_name' => $request->last_name,
            'first_name' => $request->first_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'gender' => $request->gender,
            'phone' => $request->phone,
            'profession' => $request->profession,
            'date_of_birth' => $request->date_of_birth,
            'isAdmin' => $request->boolean('isAdmin') ?? 0,
            'nationality' => $request->nationality,
            'current_city' => $request->current_city,
            'profile_photo' => $profilePhotoPath
        ]);

        $token = $user->createToken("API TOKEN")->plainTextToken;

        return response()->json([
            'status' => 200,
            'message' => 'Inscription réussie',
            'token' => $token,
            'user' => $user
        ], 200);
    }

    public function logout() {

        auth()->user()->tokens()->delete();

        return response()->json([
          'status' => 200,
          'message' => 'Deconnecté'
        ],200);
    }

}
