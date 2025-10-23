<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CalculatorController extends Controller
{
    /**
     * Show the calculator form.
     */
    public function index()
    {
        $history = session()->get('calc_history', []);
        return view('calculator', ['history' => $history]);
    }

    /**
     * Process the calculation.
     */
    public function calculate(Request $request)
    {
        $data = $request->validate([
            'a' => 'required|numeric',
            'b' => 'required|numeric',
            'op' => 'required|in:add,sub,mul,div',
        ]);

        $a = $data['a'];
        $b = $data['b'];
        $op = $data['op'];

        $result = null;
        $error = null;

        switch ($op) {
            case 'add':
                $result = $a + $b;
                break;
            case 'sub':
                $result = $a - $b;
                break;
            case 'mul':
                $result = $a * $b;
                break;
            case 'div':
                if ((float) $b == 0.0) {
                    $error = 'División por cero no permitida.';
                } else {
                    $result = $a / $b;
                }
                break;
        }

        // Push to session history (keep last 10)
        $history = session()->get('calc_history', []);
        $entry = [
            'a' => $a,
            'b' => $b,
            'op' => $op,
            'result' => $result,
            'error' => $error,
            'time' => now()->toDateTimeString(),
        ];
        array_unshift($history, $entry);
        $history = array_slice($history, 0, 10);
        session(['calc_history' => $history]);

        if ($request->wantsJson() || $request->ajax() || $request->header('Accept') === 'application/json') {
            return response()->json(['result' => $result, 'error' => $error, 'history' => $history]);
        }

        return view('calculator', [
            'a' => $a,
            'b' => $b,
            'op' => $op,
            'result' => $result,
            'error' => $error,
            'history' => $history,
        ]);
    }

    /**
     * Clear calculation history from session.
     */
    public function clearHistory(Request $request)
    {
        $request->session()->forget('calc_history');
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['cleared' => true]);
        }
        return redirect('/');
    }
}
