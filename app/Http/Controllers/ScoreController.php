<?php

namespace App\Http\Controllers;

use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Exception;

class ScoreController extends Controller
{
    public function index(Request $request)
    {
        try {
            $email = str_replace('Bearer ', '', $request->header('Authorization'));

            $query = Score::query();

            if (!$email) {
                $query->where('mine', 0);
            } else {
                $query->where(function ($q) use ($email) {
                    $q->where('mine', 0)
                    ->orWhere(function ($q2) use ($email) {
                        $q2->where('email', $email);
                    });
                });
            }

            $scores = $query->get()->map(function ($score) use ($email) {
                return [
                    'id' => $score->id,
                    'semester' => $score->semester,
                    'mataKuliah' => $score->mataKuliah,
                    'gambar' => pathinfo($score->gambar, PATHINFO_FILENAME),
                    'mine' => $score->email === $email ? 1 : 0
                ];
            });

            if ($scores->isEmpty()) {
                return response()->json(['message' => 'Tidak ada data skor tersedia.'], 200);
            }

            return response()->json($scores, 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $email = str_replace('Bearer ', '', $request->header('Authorization'));

            if (!$email) {
                return response()->json(['message' => 'Anda harus login terlebih dahulu'], 401);
            }

            $validated = $request->validate([
                'semester' => 'required|string|max:255',
                'mataKuliah' => 'required|string|max:255',
                'gambar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $path = $request->file('gambar')->store('gambar', 'public');

            Score::create([
                'semester' => $validated['semester'],
                'mataKuliah' => $validated['mataKuliah'],
                'gambar' => $path,
                'mine' => 1,
                'email' => $email
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Nilai berhasil ditambahkan'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $id = $request->query('id');
            $email = str_replace('Bearer ', '', $request->header('Authorization'));

            if (!$email) {
                return response()->json(['message' => 'Anda harus login terlebih dahulu'], 401);
            }

            $score = Score::where('id', $id)->where('email', $email)->first();

            if (!$score) {
                return response()->json(['message' => 'Data tidak ditemukan atau bukan milik Anda'], 404);
            }

            if ($score->gambar && Storage::disk('public')->exists($score->gambar)) {
                Storage::disk('public')->delete($score->gambar);
            }

            $score->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Nilai berhasil dihapus'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $id = $request->query('id');
            $email = str_replace('Bearer ', '', $request->header('Authorization'));

            $score = Score::where('id', $id)->where('email', $email)->first();

            if (!$score) {
                return response()->json(['message' => 'Data tidak ditemukan atau bukan milik Anda'], 404);
            }

            $request->validate([
                'semester' => 'sometimes|required|string|max:255',
                'mataKuliah' => 'sometimes|required|string|max:255',
                'gambar' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($request->has('semester')) $score->semester = $request->semester;
            if ($request->has('mataKuliah')) $score->mataKuliah = $request->mataKuliah;

            if ($request->hasFile('gambar')) {
                if ($score->gambar && Storage::disk('public')->exists($score->gambar)) {
                    Storage::disk('public')->delete($score->gambar);
                }
                $score->gambar = $request->file('gambar')->store('gambar', 'public');
            }

            $score->save();

            $score->gambar = asset('storage/' . $score->gambar);

            return response()->json([
                'status' => 'success',
                'message' => 'Nilai berhasil dihapus'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getImage(Request $request)
    {
        try {
            $id = $request->query('id');

            if (!$id) {
                return response()->json(['message' => 'Parameter id wajib diisi'], 400);
            }

            $files = Storage::disk('public')->files('gambar');

            $matchedFile = collect($files)->first(function ($file) use ($id) {
                return pathinfo($file, PATHINFO_FILENAME) === $id;
            });

            if (!$matchedFile) {
                return response()->json(['message' => 'Gambar tidak ditemukan'], 404);
            }

            $fullPath = storage_path('app/public/' . $matchedFile);

            if (!file_exists($fullPath)) {
                return response()->json(['message' => 'File gambar tidak ditemukan di server'], 404);
            }

            return Response::make(file_get_contents($fullPath), 200, [
                'Content-Type' => mime_content_type($fullPath),
                'Content-Disposition' => 'inline; filename="' . basename($fullPath) . '"'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}