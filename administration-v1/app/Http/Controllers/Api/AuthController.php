<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user (Rider or Driver)
     */
    public function register(RegisterRequest $request)
    {
        try {
            $userData = $request->validated();
            
            // Hash password
            $userData['password'] = Hash::make($userData['password']);
            
            // Set default values based on user type
            if ($userData['type'] === 'driver') {
                $userData['driver_status'] = 'pending';
                $userData['is_online'] = false;
                $userData['rating'] = 5.0;
                $userData['total_trips'] = 0;
                $userData['earnings'] = 0;
            } elseif ($userData['type'] === 'rider') {
                $userData['rider_status'] = 'active';
                $userData['rider_trips_count'] = 0;
                $userData['rider_rating'] = 5.0;
            }
            
            // Create user
            $user = User::create($userData);
            
            // Create token
            $token = $user->createToken($request->device_name ?? 'mobile')->plainTextToken;
            
            // Return response
            return response()->json([
                'success' => true,
                'message' => ucfirst($user->type) . ' registered successfully',
                'data' => [
                    'user' => $this->getUserResponse($user),
                    'token' => $token,
                ]
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user (Rider or Driver)
     */
    public function login(LoginRequest $request)
    {
        try {
            // Find user by email or phone
            $user = User::where('email', $request->email)
                ->orWhere('phone', $request->phone)
                ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            // Check if user is active
            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is deactivated. Please contact support.',
                ], 403);
            }

            // Check if user is suspended
            if ($user->is_suspended) {
                $message = 'Your account is suspended';
                if ($user->suspended_until) {
                    $message .= ' until ' . $user->suspended_until->format('Y-m-d H:i:s');
                }
                if ($user->suspension_reason) {
                    $message .= '. Reason: ' . $user->suspension_reason;
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 403);
            }

            // Update last login
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            // Create token
            $token = $user->createToken($request->device_name)->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $this->getUserResponse($user),
                    'token' => $token,
                ]
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'errors' => $e->errors()
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user profile
     */
    public function profile(Request $request)
    {
        try {
            $user = $request->user();
            
            return response()->json([
                'success' => true,
                'message' => 'Profile retrieved successfully',
                'data' => [
                    'user' => $this->getUserResponse($user),
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update driver online status
     */
    public function updateOnlineStatus(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user->isDriver()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only drivers can update online status',
                ], 403);
            }

            $request->validate([
                'is_online' => 'required|boolean',
            ]);

            $user->update([
                'is_online' => $request->is_online,
                'last_online_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Online status updated successfully',
                'data' => [
                    'is_online' => $user->is_online,
                    'last_online_at' => $user->last_online_at,
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update online status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update driver location
     */
    public function updateLocation(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user->isDriver()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only drivers can update location',
                ], 403);
            }

            $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'speed' => 'nullable|numeric|min:0',
                'heading' => 'nullable|numeric|between:0,360',
            ]);

            $result = $user->updateLocation(
                $request->latitude,
                $request->longitude,
                $request->speed,
                $request->heading
            );

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Location updated successfully',
                    'data' => [
                        'current_latitude' => $user->current_latitude,
                        'current_longitude' => $user->current_longitude,
                    ]
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update location',
            ], 400);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update location',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get formatted user response
     */
    private function getUserResponse(User $user): array
    {
        $response = [
            'id' => $user->id,
            'type' => $user->type,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
            'date_of_birth' => $user->date_of_birth,
            'gender' => $user->gender,
            'address' => $user->address,
            'city' => $user->city,
            'country' => $user->country,
            'postal_code' => $user->postal_code,
            'is_verified' => $user->is_verified,
            'is_active' => $user->is_active,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];

        // Add driver-specific fields
        if ($user->isDriver()) {
            $response = array_merge($response, [
                'driver_license_number' => $user->driver_license_number,
                'driver_license_expiry' => $user->driver_license_expiry,
                'vehicle_type' => $user->vehicle_type,
                'vehicle_model' => $user->vehicle_model,
                'vehicle_year' => $user->vehicle_year,
                'vehicle_plate_number' => $user->vehicle_plate_number,
                'vehicle_color' => $user->vehicle_color,
                'current_latitude' => $user->current_latitude,
                'current_longitude' => $user->current_longitude,
                'driver_status' => $user->driver_status,
                'rating' => $user->rating,
                'total_trips' => $user->total_trips,
                'earnings' => $user->earnings,
                'is_online' => $user->is_online,
                'last_online_at' => $user->last_online_at,
                'has_all_documents_verified' => $user->has_all_documents_verified,
            ]);
        }

        // Add rider-specific fields
        if ($user->isRider()) {
            $response = array_merge($response, [
                'rider_status' => $user->rider_status,
                'rider_trips_count' => $user->rider_trips_count,
                'rider_rating' => $user->rider_rating,
                'preferred_payment_method' => $user->preferred_payment_method,
            ]);
        }

        return $response;
    }
}