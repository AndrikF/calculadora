<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Calculadora</title>
    <style>
        :root{
            --bg:#1f1b1e; --panel:#2a2629; --button:#3a3538; --accent:#d63bf0; --text:#fff; --muted:#bdb6b9;
        }
        html,body{height:100%;margin:0}
        body{display:flex;align-items:center;justify-content:center;background:linear-gradient(180deg,#171416,#2a2729);font-family:Segoe UI,Roboto,Arial;color:var(--text)}
        .calc{width:360px;background:var(--panel);border-radius:12px;padding:18px;box-shadow:0 10px 30px rgba(0,0,0,.6)}
    .display{height:100px;background:#0f0d0e;border-radius:8px;color:var(--text);display:flex;flex-direction:column;justify-content:center;padding:12px 14px;margin-bottom:12px}
    .small{color:var(--muted);font-size:13px}
    .big{font-size:40px;text-align:right;line-height:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .pad{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
        button.key{height:56px;border-radius:8px;border:0;background:var(--button);color:var(--text);font-size:18px}
        button.key.op{background:#422f2f}
        button.key.eq{background:var(--accent);color:#111}
        button.key.wide{grid-column:1/3}
        .muted{background:#3b393b}
    </style>
</head>
<body>
    <div class="calc" role="application" aria-label="Calculadora">
        <div class="display">
            <div class="small" id="history">&nbsp;</div>
            <div class="big" id="screen">0</div>
        </div>

        <div style="display:flex;gap:12px;align-items:center;margin-bottom:10px">
            <div style="flex:1;display:flex;gap:8px;align-items:center">
              
            </div>
            <div>
              
            </div>
        </div>

        <div class="pad">
            <button class="key muted" data-action="clear">CE</button>
            <button class="key muted" data-action="neg">+/-</button>
            <button class="key muted" data-action="percent">%</button>
            <button class="key op" data-action="op" data-value="/">÷</button>

            <button class="key" data-action="digit">7</button>
            <button class="key" data-action="digit">8</button>
            <button class="key" data-action="digit">9</button>
            <button class="key op" data-action="op" data-value="*">×</button>

            <button class="key" data-action="digit">4</button>
            <button class="key" data-action="digit">5</button>
            <button class="key" data-action="digit">6</button>
            <button class="key op" data-action="op" data-value="-">−</button>

            <button class="key" data-action="digit">1</button>
            <button class="key" data-action="digit">2</button>
            <button class="key" data-action="digit">3</button>
            <button class="key op" data-action="op" data-value="+">+</button>

            <button class="key wide" data-action="digit">0</button>
            <button class="key" data-action="dot">.</button>
            <button class="key eq" data-action="equals">=</button>
        </div>
    </div>

    <script>
        // Simple client-side calculator logic mimicking the Windows layout
        (function(){
            const screen = document.getElementById('screen')
            const history = document.getElementById('history')

            let current = '0', prev = null, op = null, overwrite = false
            const MAX_CHARS = 14; // visible limit before shrinking or switching to exponential

            function formatDisplayValue(val){
                if(val === 'Error' || val === 'NaN' || val === 'Infinity') return val
                const n = Number(val)
                if(!isFinite(n)) return val

                let s = String(val)
                const absN = Math.abs(n)

                if(s.length > MAX_CHARS){
                    if(absN >= 1e12 || (absN > 0 && absN < 1e-6)){
                        s = n.toExponential(8)
                    } else {
                        const intLen = Math.floor(absN).toString().length
                        const maxDecimals = Math.max(0, MAX_CHARS - intLen - 1)
                        s = Number(n.toFixed(Math.min(maxDecimals, 8))).toString()
                        if(s.length > MAX_CHARS) s = n.toExponential(8)
                    }
                }
                return s
            }

            function adjustFontSize(text){
                const base = 40; const min = 18
                const len = String(text).length
                if(len <= 10) screen.style.fontSize = base + 'px'
                else {
                    const extra = Math.min(20, len - 10)
                    const size = Math.max(min, base - extra * 1.2)
                    screen.style.fontSize = Math.round(size) + 'px'
                }
            }

            function update(){
                const shown = formatDisplayValue(current)
                screen.textContent = shown
                adjustFontSize(shown)
                history.textContent = prev ? (prev + (op||'')) : ''
            }

            function inputDigit(d){
                if(overwrite){ current = d; overwrite = false; }
                else if(current === '0') current = d
                else {
                    if(current.replace('-', '').replace('.', '').length >= 20) return
                    current += d
                }
                if(current.length > 40) current = current.slice(0,40)
                update()
            }

            function inputDot(){
                if(overwrite){ current = '0.'; overwrite=false; return update(); }
                if(!current.includes('.')){ current += '.'; update(); }
            }

            function clear(){ current='0'; prev=null; op=null; overwrite=false; update(); }

            function negate(){ if(current === '0') return; current = (parseFloat(current)*-1).toString(); update(); }

            function percent(){ current = (parseFloat(current)/100).toString(); update(); }

            function chooseOp(o){
                if(prev === null){ prev = current; op = o; overwrite = true; }
                else if(!overwrite){ compute(); op = o; }
                else{ op = o; }
                update()
            }

            async function compute(){
                if(prev === null || !op) return;
                const a = parseFloat(prev), b = parseFloat(current);
                let res = 0;
                if(op === '+') res = a + b;
                if(op === '-') res = a - b;
                if(op === '*') res = a * b;
                if(op === '/'){
                    if(b === 0){ current = 'Error'; prev = null; op = null; overwrite = true; return update(); }
                    res = a / b;
                }
                // Round result to avoid floating noise and limit length
                let rounded = Math.round((res + Number.EPSILON) * 1e12)/1e12
                current = String(rounded)
                // send to server to store history (best-effort)
                try {
                    await fetch('{{ url('/api/calc') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type':'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept':'application/json'
                        },
                        body: JSON.stringify({ a: prev, b: current, op: (op==='+'?'add':(op==='-'?'sub':(op==='*'?'mul':'div'))) })
                    })
                } catch(e){ /* ignore network errors */ }

                prev = null; op = null; overwrite = true; update();
            }

            document.querySelectorAll('button.key').forEach(btn=>{
                btn.addEventListener('click', ()=>{
                    const action = btn.dataset.action
                    if(action === 'digit') inputDigit(btn.textContent.trim())
                    else if(action === 'dot') inputDot()
                    else if(action === 'clear') clear()
                    else if(action === 'neg') negate()
                    else if(action === 'percent') percent()
                    else if(action === 'op') chooseOp(btn.dataset.value)
                    else if(action === 'equals') compute()
                })
            })

            // keyboard support (basic)
            window.addEventListener('keydown', (e)=>{
                if(e.key >= '0' && e.key <= '9') inputDigit(e.key)
                else if(e.key === '.') inputDot()
                else if(e.key === 'Enter' || e.key === '=') compute()
                else if(e.key === 'Backspace') current = current.slice(0,-1) || '0', update()
                else if(['+','-','*','/'].includes(e.key)) chooseOp(e.key)
            })

            update()

          
        })()
    </script>
</body>
</html>
