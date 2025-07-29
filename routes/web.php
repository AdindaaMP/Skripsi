<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\TrainerController;
use App\Http\Controllers\ProctorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdministratorController;
use App\Http\Controllers\TrainingGroupController;
use App\Http\Controllers\QuestionnaireController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BiodataController;

/*
|--------------------------------------------------------------------------
| Halaman Utama
|--------------------------------------------------------------------------
*/

Route::get('/', [DashboardController::class, 'index'])->name('welcome');

/*
|--------------------------------------------------------------------------
| Login & Logout
|--------------------------------------------------------------------------
*/

// Hapus route login manual dan proses login, redirect ke welcome jika ada yang akses /login/{role} atau /login

// Route login admin manual
Route::get('/login/admin', function () {
    return view('auth.login_admin');
})->name('login.admin');

Route::post('/login/admin', function (\Illuminate\Http\Request $request) {
    $credentials = $request->only('email', 'password');
    $credentials['role'] = 'admin';
    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->route('admin.dashboard');
    }
    return back()->with('error', 'Email atau password salah, atau Anda bukan admin.');
})->name('login.admin.submit');

// Route login manual (form login) - redirect ke welcome
Route::get('/login/{role}', function () {
    return redirect()->route('welcome');
});
// Route proses login manual - redirect ke welcome
Route::post('/login', function () {
    return redirect()->route('welcome');
});

// Logout
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('welcome'); 
})->name('logout');

Route::get('login/microsoft/{role}', [AuthController::class, 'redirectToMicrosoft'])->name('login.microsoft');
Route::get('login/microsoft/callback', [AuthController::class, 'handleMicrosoftCallback']);
Route::get('login/microsoft/{role}/callback', [AuthController::class, 'handleMicrosoftCallback'])->where('role', 'admin|user|trainer|proctor');

/*
|--------------------------------------------------------------------------
| Biodata Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/biodata', [BiodataController::class, 'show'])->name('biodata.show');
    Route::post('/biodata', [BiodataController::class, 'store'])->name('biodata.store');
    Route::get('/api/jurusan/{nim}', [BiodataController::class, 'getJurusan'])->name('api.jurusan');
});

/*
|--------------------------------------------------------------------------
| Admin Route Group
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::resource('sertifikasi', ActivityController::class);
    Route::resource('trainer', TrainerController::class);
    Route::resource('proctor', ProctorController::class);
    Route::resource('user', UserController::class);
    Route::resource('administrator', AdministratorController::class);
    Route::resource('sertifikasi.groups', TrainingGroupController::class)->except(['index']);
    Route::get('/users/search', [UserController::class, 'search'])->name('users.search');
    Route::post('/groups/{group}/add-user', [TrainingGroupController::class, 'addUser'])->name('groups.addUser');
    Route::delete('/groups/{group}/remove-user/{user}', [TrainingGroupController::class, 'removeUser'])->name('groups.removeUser');
    Route::get('/fix-program-ids', [TrainingGroupController::class, 'fixProgramIds'])->name('fix.program.ids');
    Route::get('/debug-program-data', [TrainingGroupController::class, 'debugProgramData'])->name('debug.program.data');
    Route::get('/refresh-program-relations', [TrainingGroupController::class, 'refreshProgramRelations'])->name('refresh.program.relations');
    Route::get('/check-program-relation', [TrainingGroupController::class, 'checkProgramRelation'])->name('check.program.relation');
    Route::get('/fix-program-by-group-name', [TrainingGroupController::class, 'fixProgramByGroupName'])->name('fix.program.by.group.name');
    Route::get('/fix-training-group-program/{groupId}/{programId}', [TrainingGroupController::class, 'fixSpecificTrainingGroupProgram'])->name('fix.training.group.program');
    Route::get('/check-and-fix-training-groups', [TrainingGroupController::class, 'checkAndFixTrainingGroups'])->name('check.and.fix.training.groups');
    Route::get('/check-and-fix-questionnaires', [TrainingGroupController::class, 'checkAndFixQuestionnaires'])->name('check.and.fix.questionnaires');
    Route::get('/check-and-fix-all-data', [TrainingGroupController::class, 'checkAndFixAllData'])->name('check.and.fix.all.data');
    Route::get('/fix-data-simple', function() {
        try {
            $results = [];
            
            // Fix missing programs
            $groupsWithoutProgram = \App\Models\TrainingGroup::whereNull('program_id')->get();
            $firstProgram = \App\Models\Program::first();
            if ($firstProgram) {
                foreach ($groupsWithoutProgram as $group) {
                    $group->update(['program_id' => $firstProgram->id]);
                }
            }
            $results['programs_fixed'] = $groupsWithoutProgram->count();
            
            // Fix invalid programs
            $groups = \App\Models\TrainingGroup::all();
            $invalidFixed = 0;
            foreach ($groups as $group) {
                if ($group->program_id && !\App\Models\Program::find($group->program_id)) {
                    $group->update(['program_id' => $firstProgram->id]);
                    $invalidFixed++;
                }
            }
            $results['invalid_programs_fixed'] = $invalidFixed;
            
            // Fix missing questionnaires
            $questionnairesFixed = 0;
            foreach ($groups as $group) {
                $questionnaires = \App\Models\Questionnaire::where('training_group_id', $group->id)->get();
                if ($questionnaires->isEmpty() && $group->program_id) {
                    try {
                        $group->load('program');
                        if ($group->program) {
                            $group->cloneQuestionnairesFromProgram();
                            $questionnairesFixed++;
                        }
                    } catch (\Exception $e) {
                        $results['errors'][] = "Error for group {$group->id}: " . $e->getMessage();
                    }
                }
            }
            $results['questionnaires_fixed'] = $questionnairesFixed;
            
            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    })->name('fix.data.simple');

    Route::get('/export-evaluasi/{activity}', [AdminController::class, 'exportCsv'])->name('export.evaluasi');

    Route::prefix('kuesioner')->name('kuesioner.')->group(function () {
        Route::get('/manage/{group}', [QuestionnaireController::class, 'index'])->name('index');
        Route::post('/store', [QuestionnaireController::class, 'store'])->name('store');
        Route::get('/hasil/{group}', [QuestionnaireController::class, 'results'])->name('results');
        Route::get('/hasil/{group}/export', [QuestionnaireController::class, 'exportCsv'])->name('export');
        Route::put('/{questionnaire}', [QuestionnaireController::class, 'update'])->name('update');
        Route::delete('/{questionnaire}', [QuestionnaireController::class, 'destroy'])->name('destroy');
        Route::post('/update-order', [QuestionnaireController::class, 'updateOrder'])->name('updateOrder');
    });

    // Program Questionnaires Management
    Route::prefix('program-questionnaires')->name('program.questionnaires.')->group(function () {
        Route::get('/{program}', [App\Http\Controllers\ProgramQuestionnaireController::class, 'index'])->name('index');
        Route::post('/{program}', [App\Http\Controllers\ProgramQuestionnaireController::class, 'store'])->name('store');
        Route::put('/{program}/{questionnaire}', [App\Http\Controllers\ProgramQuestionnaireController::class, 'update'])->name('update');
        Route::delete('/{program}/{questionnaire}', [App\Http\Controllers\ProgramQuestionnaireController::class, 'destroy'])->name('destroy');
        Route::post('/{program}/update-order', [App\Http\Controllers\ProgramQuestionnaireController::class, 'updateOrder'])->name('updateOrder');
        Route::post('/{program}/sync', [App\Http\Controllers\ProgramQuestionnaireController::class, 'syncToGroups'])->name('sync');
    });

    Route::post('/groups/{group}/invite-user', [\App\Http\Controllers\TrainingGroupController::class, 'inviteUser'])->name('groups.inviteUser');
});

/*
|--------------------------------------------------------------------------
| Pengguna Biasa (User, Trainer, Proctor)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/trainer/hasil-evaluasi/{group}', [App\Http\Controllers\TrainerPageController::class, 'showResults'])->name('trainer.results');
    Route::get('/trainer/profil', [App\Http\Controllers\TrainerPageController::class, 'profile'])->name('trainer.profile');
    Route::get('/proctor/monitoring/{group}', [App\Http\Controllers\ProctorPageController::class, 'showMonitoring'])->name('proctor.monitoring');
    Route::get('/proctor/ketercapaian', [App\Http\Controllers\ProctorPageController::class, 'showKetercapaian'])->name('proctor.ketercapaian');
});

// Routes yang memerlukan pengecekan biodata
Route::middleware(['auth'])->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home.user');
    Route::get('/groups/{group}/evaluasi', [AnswerController::class, 'form'])->name('groups.evaluasi.form');
    Route::post('/groups/{group}/evaluasi', [AnswerController::class, 'submit'])->name('groups.evaluasi.submit');
});


