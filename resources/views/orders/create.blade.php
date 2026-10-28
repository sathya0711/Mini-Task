<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Store Billing – New Order</title>
<style>
  :root{
    --navy:#1f2a3c; --ink:#1e293b; --muted:#64748b; --line:#cbd5e1; --panel:#f1f5f9;
    --blue:#2f6db5; --green:#1f8a3b; --red:#b42318;
    --amber-bg:#fff9e6; --amber-line:#e08a00; --amber-ink:#8a4b00;
  }
  *{box-sizing:border-box}
  body{margin:0;background:#fff;color:var(--ink);font:14px/1.4 "Segoe UI",system-ui,sans-serif}
  header{background:var(--navy);color:#fff;padding:14px 24px;display:flex;justify-content:space-between;align-items:center}
  header h1{margin:0;font-size:17px;font-weight:700;letter-spacing:.2px}
  main{max-width:960px;margin:0 auto;padding:22px 24px 48px}
  h2{font-size:13px;margin:22px 0 8px;font-weight:700}
  label{display:block;font-size:11px;font-weight:700;color:var(--muted);margin-bottom:3px}
  input,select{width:100%;padding:8px 10px;border:1px solid var(--line);border-radius:4px;font:inherit;background:#fff}
  input:focus,select:focus,button:focus-visible{outline:2px solid var(--blue);outline-offset:1px}
  .row{display:flex;gap:16px;flex-wrap:wrap}
  .row>div{flex:1 1 220px;max-width:300px}
  .split{display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap}
  .left{flex:1 1 520px;min-width:0}
  table{width:100%;border-collapse:collapse}
  th{background:#e2e8f0;text-align:left;font-size:12px;padding:8px 10px;border:1px solid var(--line)}
  td{padding:4px 6px;border:1px solid var(--line)}
  td input,td select{border:0;padding:5px 4px}
  td.num,th.num{text-align:right}
  td.line{text-align:right;padding-right:10px;white-space:nowrap}
  .x{border:0;background:none;color:var(--red);cursor:pointer;font-size:16px}
  .btn{border:0;border-radius:4px;color:#fff;font:700 13px inherit;padding:10px 16px;cursor:pointer}
  .btn:disabled{opacity:.55;cursor:not-allowed}
  .add{background:var(--blue);float:right;margin-top:8px}
  .alert{flex:0 0 220px;background:var(--amber-bg);border:1px solid var(--amber-line);border-radius:4px;padding:10px 12px;min-height:150px}
  .alert h3{margin:0 0 6px;font-size:12px;color:var(--amber-ink)}
  .alert ul{margin:0;padding-left:16px;font-size:12px;color:var(--amber-ink)}
  .pay{display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap}
  .box{background:var(--panel);border:1px solid var(--line);border-radius:4px;padding:12px 14px;width:340px;max-width:100%}
  .sum{display:flex;justify-content:space-between;margin:3px 0}
  .sum.total{font-weight:700;border-top:1px dashed var(--line);margin-top:6px;padding-top:6px}
  .bal{display:flex;justify-content:space-between;gap:8px;font-weight:700;margin-top:10px}
  .bal small{font-weight:400;color:var(--muted);text-align:right}
  .go{background:var(--green)}
  .hint{font-size:11px;color:var(--muted);margin-top:6px;max-width:170px}
  .msg{margin-top:14px;padding:10px 12px;border-radius:4px;font-size:13px}
  .msg.err{background:#fef3f2;border:1px solid #fda29b;color:var(--red)}
  .msg.ok{background:#ecfdf3;border:1px solid #86d6a4;color:#14602b}
  .msg ul{margin:4px 0 0;padding-left:18px}
  .bill{margin-top:18px;border:1px solid var(--line);border-radius:4px;padding:14px 16px}
  .bill table td,.bill table th{font-size:12px}
  @media print{header,.no-print{display:none}}
</style>
</head>
<body>
<header><h1>Store Billing – New Order</h1></header>

<main>
  <h2>Customer</h2>
  <div class="row">
    <div><label for="email">Email</label><input id="email" name="email" type="email" placeholder="e.g. thomas@example.com" autocomplete="off"></div>
    <div><label for="name">Name</label><input id="name" name="name" placeholder="Auto-filled if email exists"></div>
  </div>

  <h2>Products</h2>
  <div class="split">
    <div class="left">
      <table>
        <thead><tr><th>Product</th><th style="width:80px">Qty</th><th class="num" style="width:90px">Price</th><th class="num" style="width:110px">Line total</th><th style="width:30px"></th></tr></thead>
        <tbody id="lines"></tbody>
      </table>
      <button class="btn add" id="addRow" type="button">+ Add product</button>
    </div>
    <aside class="alert" aria-live="polite">
      <h3>Low stock alert</h3>
      <ul id="low"><li>Loading…</li></ul>
    </aside>
  </div>

  <h2>Payment</h2>
  <div class="pay">
    <div class="box">
      <div class="sum"><span>Subtotal</span><b id="sub">₹0.00</b></div>
      <div class="sum"><span>Tax</span><b id="tax">₹0.00</b></div>
      <div class="sum total"><span>Grand total</span><b id="grand">₹0.00</b></div>
      <div style="margin-top:12px"><label for="paid">Amount given by customer</label><input id="paid" type="number" min="0" step="0.01" placeholder="₹250"></div>
      <div class="bal"><span>Balance to return</span><small id="bal">–</small></div>
    </div>
    <div class="no-print">
      <button class="btn go" id="gen" type="button">Generate bill</button>
      <div class="hint">Shows the bill on page + 
       emails PDF to customer.</div>
    </div>
  </div>

  <div id="msg" role="status"></div>
  <div id="bill"></div>
</main>

<script>

const API = "{{ url('/api') }}";
const EP = {
  products: `${API}/products`,                      
  lowStock: `${API}/products/low-stock`,            
  history:  e => `${API}/customers/${encodeURIComponent(e)}/orders`, 
  order:    `${API}/orders`                          
};

const $ = id => document.getElementById(id);
const rs = n => '₹' + (Math.round(n * 100) / 100).toFixed(2);
const list = j => Array.isArray(j) ? j : (j.data ?? []);
const stockOf = p => p.stock ?? p.stock_on_hand ?? p.stock_quantity ?? 0;
const taxOf = p => Number(p.tax_percentage ?? p.tax_percent ?? p.tax ?? 0);
let products = [];

const headers = {'Content-Type':'application/json','Accept':'application/json'};
async function get(url){ const r = await fetch(url,{headers}); if(!r.ok) throw r; return r.json(); }


function addRow(){
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td><select class="p"><option value="">Select product…</option>${
      products.map(p=>`<option value="${p.id}" ${stockOf(p)<1?'disabled':''}>${p.name} (${stockOf(p)} in stock)</option>`).join('')
    }</select></td>
    <td><input class="q" type="number" min="1" value="1"></td>
    <td class="line price">–</td>
    <td class="line lt">–</td>
    <td><button class="x" type="button" title="Remove row" aria-label="Remove row">×</button></td>`;
  tr.querySelector('.p').onchange = calc;
  tr.querySelector('.q').oninput = calc;
  tr.querySelector('.x').onclick = () => { tr.remove(); calc(); };
  $('lines').appendChild(tr);
}

function rows(){
  return [...$('lines').children].map(tr=>{
    const p = products.find(x=>String(x.id)===tr.querySelector('.p').value);
    const q = Math.max(0, parseInt(tr.querySelector('.q').value)||0);
    return {tr,p,q};
  });
}


function calc(){
  let sub=0, tax=0;
  rows().forEach(({tr,p,q})=>{
    if(!p){ tr.querySelector('.price').textContent='–'; tr.querySelector('.lt').textContent='–'; return; }
    const base = Number(p.price)*q, t = base*taxOf(p)/100;
    sub+=base; tax+=t;
    tr.querySelector('.price').textContent = rs(Number(p.price));
    tr.querySelector('.lt').textContent = rs(base+t);
  });
  const grand = sub+tax;
  $('sub').textContent=rs(sub); $('tax').textContent=rs(tax); $('grand').textContent=rs(grand);
  balance(grand);
  return {sub,tax,grand};
}


function balance(grand){
  const paid = parseFloat($('paid').value);
  if(isNaN(paid) || grand<=0){ $('bal').textContent='–'; return; }
  const diff = Math.round((paid-grand)*100)/100;
  if(diff<0){ $('bal').innerHTML = `<span style="color:var(--red)">Short by ${rs(-diff)}</span>`; return; }
  let rem = Math.round(diff), parts=[];
  [2000,500,200,100,50,20,10,5,2,1].forEach(d=>{ const n=Math.floor(rem/d); if(n){parts.push(`${n}×${d}`); rem-=n*d;} });
  $('bal').innerHTML = `${rs(diff)}<br><small>${parts.join(' + ')||''}</small>`;
}
$('paid').oninput = () => calc();


async function loadLow(){
  try{
    const items = list(await get(EP.lowStock));
    $('low').innerHTML = items.length
      ? items.map(p=>`<li>${p.name} – ${stockOf(p)} unit${stockOf(p)==1?'':'s'} left</li>`).join('')
      : '<li>All products are well stocked.</li>';
  }catch{ $('low').innerHTML='<li>Could not load low-stock list.</li>'; }
}


let autoFilled = false;
$('name').addEventListener('input', () => { autoFilled = false; });
$('email').addEventListener('change', async () => {
  const e = $('email').value.trim();
  if (!e) return;
  try {
    const j = await get(EP.history(e));
    const n = j.customer?.name;
    if (n && (!$('name').value || autoFilled)) { $('name').value = n; autoFilled = true; }
  } catch {
    if (autoFilled) { $('name').value = ''; autoFilled = false; }
  }
});


function show(kind, html){ $('msg').innerHTML = `<div class="msg ${kind}">${html}</div>`; }

$('gen').onclick = async ()=>{
  $('msg').innerHTML=''; $('bill').innerHTML='';
  const items = rows().filter(r=>r.p && r.q>0).map(r=>({product_id:r.p.id, quantity:r.q}));
  if(!$('email').value.trim() || !$('name').value.trim()) return show('err','Enter the customer email and name.');
  if(!items.length) return show('err','Add at least one product with a quantity.');
  const {grand} = calc();
  const paid = parseFloat($('paid').value);
  if(!isNaN(paid) && paid<grand) return show('err',`Amount given is ${rs(grand-paid)} short of the grand total.`);

  $('gen').disabled = true;
  try{
    const r = await fetch(EP.order,{method:'POST',headers,body:JSON.stringify({
      customer:{email:$('email').value.trim(), name:$('name').value.trim()}, items
    })});
    const j = await r.json().catch(()=>({}));
    if(!r.ok){
      const errs = j.errors ? Object.values(j.errors).flat() : [j.message||'Order failed.'];
      return show('err',`<b>Order not placed.</b><ul>${errs.map(e=>`<li>${e}</li>`).join('')}</ul>`);
    }
    renderBill(j.data ?? j, grand, paid);
    show('ok','Order placed. Confirmation email queued.');
    await Promise.all([loadProducts(), loadLow()]);   // refresh stock
    $('lines').innerHTML=''; addRow(); calc();
  }catch{ show('err','Could not reach the server. Try again.'); }
  finally{ $('gen').disabled=false; }
};

function renderBill(o, grand, paid){
  const lines = o.items ?? o.lines ?? o.order_items ?? [];
  const body = lines.map(l=>`<tr><td>${l.product?.name ?? l.name ?? ('#'+l.product_id)}</td><td class="num">${l.quantity}</td><td class="num">${rs(Number(l.unit_price ?? l.price ?? 0))}</td><td class="num">${rs(Number(l.line_total ?? l.total ?? 0))}</td></tr>`).join('');
  $('bill').innerHTML = `<div class="bill">
    <b>Bill${o.id?` #${o.id}`:''}</b> – ${$('name').value} (${$('email').value})
    <table style="margin-top:8px"><thead><tr><th>Product</th><th class="num">Qty</th><th class="num">Price</th><th class="num">Line total</th></tr></thead><tbody>${body}</tbody></table>
    <div class="sum" style="margin-top:8px"><span>Subtotal</span><b>${rs(Number(o.subtotal ?? 0))}</b></div>
    <div class="sum"><span>Tax</span><b>${rs(Number(o.tax_total ?? o.tax ?? 0))}</b></div>
    <div class="sum total"><span>Grand total</span><b>${rs(Number(o.grand_total ?? o.total ?? grand))}</b></div>
    ${isNaN(paid)?'':`<div class="sum"><span>Paid</span><b>${rs(paid)}</b></div><div class="sum"><span>Balance returned</span><b>${rs(paid-grand)}</b></div>`}
    <button class="btn add no-print" style="float:none;margin-top:10px" onclick="window.print()">Print bill</button></div>`;
}

async function loadProducts(){
  try{ products = list(await get(EP.products)); }
  catch{ show('err','Could not load products.'); }
}

$('addRow').onclick = addRow;
(async()=>{ await loadProducts(); addRow(); loadLow(); })();
</script>
</body>
</html>