const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync('assets/main.js', 'utf8').replace(/\r\n/g, '\n');
const loader = source.slice(source.indexOf('  let closeActiveStory'), source.indexOf("  (function(){\n    const btn = document.getElementById('back-to-top');"));
function node() {
  const classes = new Set(), attrs = {}, listeners = {};
  return { innerHTML: '', textContent: '', scrollTop: 0, listeners,
    classList: { add: s => classes.add(s), remove: s => classes.delete(s), contains: s => classes.has(s) },
    setAttribute: (k,v) => attrs[k]=v, removeAttribute: k => delete attrs[k],
    addEventListener: (k,v) => listeners[k]=v,
    querySelector(s) { return this.children[s] ||= node(); }, children: {} };
}
async function run(kind) {
  const nodes = {}, handlers = {}, requests = [], timers = new Set();
  const doc = { body: node(), getElementById(id) { return nodes[id] ||= node(); },
    addEventListener(k,v) { (handlers[k] ||= []).push(v); } };
  const context = { document: doc, window: { addEventListener() {} }, location: {origin:'https://example.test',hash:'',pathname:'/',search:''},
    history: {replaceState() {}}, htFocusTrap: () => ({activate(){},deactivate(){}}), AbortController,
    setTimeout(fn) { timers.add(fn); return fn; }, clearTimeout(fn) {timers.delete(fn);},
    fetch(url, options) { return new Promise((resolve,reject) => { requests.push({url,signal:options.signal,resolve,reject}); }); } };
  vm.runInNewContext(loader, context);
  const modal = nodes[kind+'-modal'], body=nodes[kind+'-modal-body'];
  function click(slug, modifiers={}) { handlers.click.forEach(fn=>fn({button:0,...modifiers,preventDefault(){},target:{closest(s){return s==='[data-open-'+kind+']'?{getAttribute:()=>slug}:null;}}})); }
  const settle = async () => { for(let i=0;i<8;i++) await Promise.resolve(); };
  const success = (r,text) => r.resolve({ok:true,json:async()=>({html:text,title:text})});
  click('first'); assert(modal.classList.contains('active')); assert(body.innerHTML.includes('Loading'));
  click('second'); assert(requests[0].signal.aborted);
  success(requests[1],'second content'); await settle();
  success(requests[0],'stale content'); await settle(); assert.equal(body.innerHTML,'second content');
  click('closed'); modal.querySelector(kind==='book'?'.book-modal-close':'[data-close-webnovel]').listeners.click();
  assert(requests[2].signal.aborted); success(requests[2],'late content'); await settle(); assert(!modal.classList.contains('active')); assert.notEqual(body.innerHTML,'late content');
  click('failure'); requests[3].reject(new Error('offline')); await settle(); assert(body.innerHTML.includes('Try again')); assert.equal(body.querySelector('a').href,'https://example.test/'+kind+'/failure/');
  body.querySelector('button').listeners.click(); success(requests[4],'retry content'); await settle(); assert.equal(body.innerHTML,'retry content');
  click('second'); await settle(); assert.equal(requests.length,5); assert.equal(body.innerHTML,'second content');
  click('modified',{ctrlKey:true}); assert.equal(requests.length,5);
  click('timeout'); [...timers].forEach(fn=>fn()); assert(requests[5].signal.aborted); requests[5].reject(new Error('aborted')); await settle(); assert(body.innerHTML.includes('Try again'));
  click('invalid'); requests[6].resolve({ok:true,json:async()=>({})}); await settle(); assert(body.innerHTML.includes('Try again'));
  console.log(kind+': loading, races, cancellation, retry, cache, modified clicks, timeout and invalid response passed');
}
(async()=>{await run('book');await run('webnovel');})().catch(e=>{console.error(e);process.exitCode=1;});

