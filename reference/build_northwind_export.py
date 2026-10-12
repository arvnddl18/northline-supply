from pathlib import Path
import base64
import posixpath
import re

ROOT = Path('/home/ubuntu/northwind')
OUT = ROOT / 'export'
OUT.mkdir(parents=True, exist_ok=True)

order = [
    'src/components.js',
    'src/data.js',
    'src/views/shared.js',
    'src/views/admin.js',
    'src/views/storefront.js',
    'src/views/portal.js',
    'src/views/settlement.js',
    'src/views/demo.js',
    'src/main.js',
]

def symbol(path):
    return '__nw_' + re.sub(r'[^A-Za-z0-9_$]', '_', path)

built = set()
modules = []
import_re = re.compile(r'^[ \t]*import[ \t]+\{([^}]*)\}[ \t]+from[ \t]+[\'\"]([^\'\"]+)[\'\"];[ \t]*$', re.M)
export_re = re.compile(r'^[ \t]*export[ \t]+(?:async[ \t]+)?(?:const|let|var|function|class)[ \t]+([A-Za-z_$][\w$]*)', re.M)

def strip_and_bind(path, source):
    bindings = []
    def replace_import(match):
        imported = match.group(1)
        target = posixpath.normpath(posixpath.join(posixpath.dirname(path), match.group(2)))
        if target.startswith('../') or target not in order:
            raise ValueError(f'Unexpected dependency {target} in {path}')
        if target not in built:
            raise ValueError(f'Dependency order error: {path} requires {target}')
        pairs = []
        for item in imported.split(','):
            item = item.strip()
            if not item:
                continue
            bits = re.split(r'\s+as\s+', item)
            pairs.append(bits[0] if len(bits) == 1 else f'{bits[0]}: {bits[1]}')
        bindings.append(f"  const {{ {', '.join(pairs)} }} = {symbol(target)};")
        return ''
    source = import_re.sub(replace_import, source)
    if re.search(r'^\s*import\s', source, re.M):
        raise ValueError(f'Unsupported import syntax in {path}')
    exports = export_re.findall(source)
    source = re.sub(r'^([ \t]*)export[ \t]+', r'\1', source, flags=re.M)
    if re.search(r'^\s*export\s', source, re.M):
        raise ValueError(f'Unsupported export syntax in {path}')
    return '\n'.join(bindings), source, exports

image = Path('/tmp/northwind-vase.webp').read_bytes()
image_data = 'data:image/webp;base64,' + base64.b64encode(image).decode('ascii')
prelude = f'''// Standalone export: all application modules and the product image are included in this file.
const NORTHWIND_VASE_IMAGE = '{image_data}';
const __NORTHWIND_STATIC_EXPORT__ = true;
function __nwPath() {{
  if (location.hash.startsWith('#/')) return location.hash.slice(1).replace(/\\/$/, '') || '/';
  const path = (location.pathname || '/').replace(/\\/$/, '') || '/';
  return path === '/index.html' || path.endsWith('/index.html') ? '/' : path;
}}
function __nwOrigin() {{ return location.origin === 'null' ? 'http://localhost:8000' : location.origin; }}
'''
modules.append(prelude)

for path in order:
    source = (ROOT / path).read_text()
    # Keep the custom product photograph without requiring a fourth asset file.
    source = source.replace('/manus-storage/vase_e2a3cd04.webp', '${NORTHWIND_VASE_IMAGE}')
    exports = []
    bindings, source, exports = strip_and_bind(path, source)

    if path == 'src/views/portal.js':
        source = source.replace("const expired = location.pathname === '/portal/expired';", "const expired = __nwPath() === '/portal/expired';")
        source = source.replace("const customer = decodeURIComponent(location.pathname.split('/').pop() || 'clara').toLowerCase();", "const customer = decodeURIComponent(__nwPath().split('/').pop() || 'clara').toLowerCase();")
        assert '__nwPath()' in source

    if path == 'src/main.js':
        source = source.replace("activeTab: location.hash.replace('#', '') || 'overview',", "activeTab: (location.hash.startsWith('#/') ? 'overview' : location.hash.replace('#', '')) || 'overview',")
        source = source.replace("lastAdminHash: location.hash.replace('#', '') || 'overview',", "lastAdminHash: (location.hash.startsWith('#/') ? 'overview' : location.hash.replace('#', '')) || 'overview',")
        source = source.replace("const path = location.pathname.replace(/\\/$/, '') || '/';", "const path = __nwPath();")
        source = source.replace("if (location.pathname.replace(/\\/$/, '') === path) render({ preserveFocus: false });", "if (__nwPath() === path) render({ preserveFocus: false });")
        source = source.replace('location.origin', '__nwOrigin()')
        start = source.index('function navigate(path) {')
        end = source.index('\nlet dialogFocusReturn', start)
        navigation = '''function navigate(path) {
  const setHash = value => {
    if (location.protocol === 'file:') location.hash = value;
    else history.pushState({}, '', `${location.pathname}${value}`);
  };
  if (path.startsWith('#')) {
    setHash(path);
    state.activeTab = path.slice(1) || 'overview';
    state.lastAdminHash = state.activeTab;
    render();
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }
  if (path.startsWith('/#')) {
    const anchor = path.slice(1);
    setHash(anchor);
    state.activeTab = anchor.slice(1) || 'overview';
    state.lastAdminHash = state.activeTab;
    render();
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }
  if (path === '/') {
    state.activeTab = state.lastAdminHash || 'overview';
    setHash(`#${state.activeTab}`);
  } else {
    setHash(`#${path}`);
  }
  closeDialog();
  render();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}'''
        source = source[:start] + navigation + source[end:]
        assert "const path = __nwPath();" in source
        assert "location.pathname.replace" not in source
        assert "history.pushState({}, '', path)" not in source

    key = symbol(path)
    if exports:
        return_object = '  return { ' + ', '.join(exports) + ' };\n'
    else:
        return_object = '  return {};\n'
    modules.append(f"\n// ---- {path} ----\nconst {key} = (() => {{\n{bindings}\n{source}\n{return_object}}})();\n")
    built.add(path)

app_js = ''.join(modules)
(OUT / 'app.js').write_text(app_js)
(OUT / 'styles.css').write_text((ROOT / 'src/styles.css').read_text())
(OUT / 'index.html').write_text('''<!doctype html>
<html lang="en-AU">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#FAF7F5">
  <meta name="description" content="Northwind Ceramics — considered payments for pieces made slowly by hand.">
  <title>Northwind Ceramics — Studio payments</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="./styles.css">
</head>
<body>
  <a class="skip-link" href="#main-content">Skip to main content</a>
  <div id="app"></div>
  <div id="overlay-root"></div>
  <div id="toast-stack" class="toast-stack" aria-live="polite" aria-atomic="false"></div>
  <script defer src="./app.js"></script>
</body>
</html>
''')
print(f'Created exactly three export files under {OUT}')
for p in sorted(OUT.iterdir()):
    print(f'{p.name}: {p.stat().st_size:,} bytes')
