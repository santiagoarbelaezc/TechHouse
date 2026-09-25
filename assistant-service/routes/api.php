<?php

use App\Http\Controllers\Api\AssistantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Assistant & Chatbot Routes
|--------------------------------------------------------------------------
*/
Route::prefix('assistant')->group(function () {
    Route::post('/chat', [AssistantController::class, 'chat']);
    Route::get('/conversations/{userId}', [AssistantController::class, 'userConversations']);
    Route::get('/conversations/detail/{id}', [AssistantController::class, 'show']);
});
