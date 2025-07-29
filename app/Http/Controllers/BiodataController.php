<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class BiodataController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        if ($user->biodata_confirmed) {
            return redirect()->route('home.user');
        }
        if (!$user->nim || !$user->jurusan) {
            $this->autoFillBiodata($user);
            $user = User::find($user->id);
        }
        return view('biodata.form', compact('user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nim' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();
        $nim = $request->nim;
        $jurusan = $request->jurusan ?? $this->getJurusanFromNIM($nim);
        $name = $request->name ?? $user->name;
        $email = $request->email ?? $user->email;
        $biodata_confirmed = $request->biodata_confirmed ? true : false;
        
        $user->update([
            'nim' => $nim,
            'jurusan' => $jurusan,
            'name' => $name,
            'email' => $email,
            'biodata_confirmed' => $biodata_confirmed,
        ]);

        $invites = \App\Models\GroupInvite::where('email', $user->email)->get();
        foreach ($invites as $invite) {
            if (!$user->trainingGroups()->where('training_group_id', $invite->training_group_id)->exists()) {
                $user->trainingGroups()->attach($invite->training_group_id);
            }
            $invite->delete();
        }

        return redirect()->route('home.user')->with('success', 'Biodata berhasil disimpan!');
    }

    public function getJurusan($nim)
    {
        $jurusan = $this->getJurusanFromNIM($nim);
        return response()->json(['jurusan' => $jurusan]);
    }

    private function autoFillBiodata(User $user)
    {
        $email = $user->email;
        $nim = $this->extractNIMFromEmail($email);
        if ($nim) {
            $jurusan = $this->getJurusanFromNIM($nim);
            $user->update([
                'nim' => $nim,
                'jurusan' => $jurusan,
            ]);
        }
    }

    private function extractNIMFromEmail($email)
    {
        if (!str_contains($email, '@itpln.ac.id')) {
            return null;
        }
        $localPart = explode('@', $email)[0];
        if (preg_match('/(\d{7,10})/', $localPart, $matches)) {
            $nim = $matches[1];
            if (strpos($nim, '20') !== 0) {
                $nim = '20' . $nim;
            }
            if (strlen($nim) >= 9) {
                return $nim;
            }
        }
        return null;
    }

    private function getJurusanFromNIM($nim)
    {
        if (strlen($nim) < 7) {
            return 'Jurusan Tidak Diketahui';
        }
        $kodeJurusan = substr($nim, 4, 2);
        $jurusanMap = [
            '11' => 'S1 Teknik Elektro',
            '12' => 'S1 Teknik Mesin',
            '21' => 'S1 Teknik Sipil',
            '31' => 'S1 Teknik Informatika',
            '71' => 'D3 Teknologi Listrik',
            '72' => 'D3 Teknik Mesin',
            '32' => 'S1 Sistem Informasi',
            '42' => 'S1 Teknik Industri',
            '41' => 'S1 Bisnis Energi',
            '14' => 'S1 Teknik Tenaga Listrik',
            '15' => 'S1 Teknik Sistem Energi',
        ];
        return $jurusanMap[$kodeJurusan] ?? 'Jurusan Tidak Diketahui';
    }
} 