<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use App\Models\TapQuiz;
use App\Models\TapResponse;
use Illuminate\Http\Request;

class checkIfTapQuizSubmitted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $quizId = $request->route('quizId');

        //check if user has already given quiz
        if(TapResponse::where([
            ['user_id', auth()->user()->id],
            ['tap_quiz_id', $quizId]
        ])->exists()) {
            return redirect()->route('user.tap.todayQuiz')->with('warning', 'Quiz is already submitted');
        }

        // Fetch the quiz based on the ID
        $quiz = TapQuiz::findOrFail($quizId);

        if(Carbon::now()->toDateString() !== Carbon::parse($quiz->start_date)->format('Y-m-d')) {
            // Redirect the user or return a response indicating the quiz is not published
            return redirect()->route('user.tap.todayQuiz')->with('warning', 'Quiz not conducting today.');
        }

        // Check if the quiz exists and is published
        if ($quiz && $quiz->is_published) {
            return $next($request);
        }
         // Redirect the user or return a response indicating the quiz is not published
         return redirect()->route('user.tap.todayQuiz')->with('error', 'Quiz is not published.');
    }
}
