const fs = require('fs');
const path = require('path');

module.exports = function refreshElfinder() {
    const root = path.resolve(__dirname, '../..');
    const source = process.env.ELFINDER_SOURCE || path.join(root, 'vendor/studio-42/elfinder');
    const locked = JSON.parse(fs.readFileSync(path.join(root, 'composer.lock'), 'utf8'))
        .packages.find(p => p.name === 'studio-42/elfinder').version;
    const client = fs.readFileSync(path.join(source, 'js/elfinder.min.js'), 'utf8');
    if (!client.includes('Version ' + locked + ' ')) throw new Error('Client does not match composer.lock');
    const locale = fs.readFileSync(path.join(source, 'js/i18n/elfinder.cs.js'), 'utf8');
    const bundlePath = path.join(root, 'www/js/all-back.js');
    const bundle = fs.readFileSync(bundlePath, 'utf8');
    const start = bundle.search(/\/\*!\r?\n \* elFinder - file manager for web/);
    const end = bundle.search(/\/\*!\r?\n\tColorbox 1\.6\.4/);
    if (start < 0 || end <= start || bundle.indexOf('elFinder - file manager for web', start + 12) !== -1) {
        throw new Error('Unexpected bundle boundaries; use a reviewed full scripts-a build');
    }
    // Replace only the two elFinder inputs in the established Gulp input order.
    fs.writeFileSync(bundlePath, bundle.slice(0, start) + client.trimEnd() + '\n' + locale.trimEnd() + '\n\n' + bundle.slice(end));
    // Not under www/vendor: some hosts deny every URL with a /vendor/ segment.
    const output = path.join(root, 'www/elfinder');
    // js/worker: the Info dialog's md5/sha256 and Quick Look on ZIP/TIFF load these.
    for (const directory of ['css', 'img', 'sounds', 'js/worker']) {
        fs.cpSync(path.join(source, directory), path.join(output, directory), {recursive: true});
    }
    console.log('Refreshed elFinder ' + locked + ' client, Czech locale and matching visual and worker assets');
};
