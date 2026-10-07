"""Build the data-free update package from the exact woodland redesign commit."""
from pathlib import Path, PurePosixPath
import hashlib
import json
import subprocess
import zipfile

BASE = 'e83562e22368d76b7f8c359c6a7ce34d0eb004d6'
TARGET = '8a9912e0d6be1378a7fff4387c60b6a8e8a57fbe'
ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / 'release-artifacts'
OUT.mkdir(exist_ok=True)
paths = subprocess.check_output(['git', 'diff', BASE, TARGET, '--name-only', '--diff-filter=ACMRT'], cwd=ROOT, text=True).splitlines()
files = []
payload = {}
for name in paths:
    path = PurePosixPath(name)
    assert not any(part in path.parts for part in ['storage', 'database', 'vendor', 'node_modules', '.git', 'bootstrap'])
    assert not path.name.startswith('.env') and path.suffix not in ['.sqlite', '.db', '.key', '.pem']
    assert name.startswith(('app/', 'resources/', 'public/images/woodland/', 'public/fonts/storybook/', 'docs/', 'tests/', 'scripts/')) or name == 'README.md'
    data = subprocess.check_output(['git', 'show', TARGET + ':' + name], cwd=ROOT)
    payload[name] = data
    files.append({'path': name, 'sha256': hashlib.sha256(data).hexdigest()})
manifest = {'baseCommit': BASE, 'redesignCommit': TARGET, 'files': files}
archive = OUT / 'Life-of-Pathi-woodland-update.zip'
prefix = 'Life-of-Pathi-woodland-update/'
with zipfile.ZipFile(archive, 'w', zipfile.ZIP_DEFLATED) as bundle:
    for name, data in payload.items():
        bundle.writestr(prefix + 'payload/' + name, data)
    bundle.writestr(prefix + 'Apply-Woodland-Update.ps1', payload['scripts/apply-windows-woodland-update.ps1'])
    bundle.writestr(prefix + 'update-manifest.json', json.dumps(manifest, indent=2))
    bundle.writestr(prefix + 'START-HERE.txt', 'Read payload/docs/windows-update-woodland.md. Stop Docker and back up local data before applying. No Git checkout is required.\n')
with zipfile.ZipFile(archive) as bundle:
    assert bundle.testzip() is None
    assert prefix + 'Apply-Woodland-Update.ps1' in bundle.namelist()
    assert prefix + 'payload/docs/windows-update-woodland.md' in bundle.namelist()
(OUT / 'windows-update-woodland.md').write_bytes(payload['docs/windows-update-woodland.md'])
(OUT / 'Life-of-Pathi-woodland-update.zip.sha256').write_text(hashlib.sha256(archive.read_bytes()).hexdigest() + '  ' + archive.name + '\n')
print(f'Packaged {len(files)} audited source files from {TARGET}; no runtime data or dependencies.')
