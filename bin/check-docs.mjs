#!/usr/bin/env node

import fs from 'node:fs';
import path from 'node:path';

const root = process.cwd();
const skippedDirectories = new Set(['.git', '.worktree', 'node_modules', 'vendor']);
const markdownFiles = [];
const errors = [];

function walk(directory) {
    for (const entry of fs.readdirSync(directory, { withFileTypes: true })) {
        if (entry.isDirectory() && skippedDirectories.has(entry.name)) {
            continue;
        }

        const target = path.join(directory, entry.name);

        if (entry.isDirectory()) {
            walk(target);
        } else if (entry.isFile() && entry.name.endsWith('.md')) {
            markdownFiles.push(target);
        }
    }
}

walk(root);

for (const file of markdownFiles) {
    const relativeFile = path.relative(root, file);
    const content = fs.readFileSync(file, 'utf8');

    if (!content.trim()) {
        errors.push(`${relativeFile}: file kosong`);
    }

    if (!content.endsWith('\n')) {
        errors.push(`${relativeFile}: final newline tidak ada`);
    }

    if (content.endsWith('\n\n')) {
        errors.push(`${relativeFile}: blank line berlebih di akhir file`);
    }

    for (const [index, line] of content.split('\n').entries()) {
        if (/[ \t]+$/.test(line)) {
            errors.push(`${relativeFile}:${index + 1}: trailing whitespace`);
        }
    }

    for (const match of content.matchAll(/\]\(([^)]+)\)/g)) {
        const destination = match[1].trim().split(/\s+/)[0];

        if (!destination || /^(?:https?:|mailto:|#)/.test(destination)) {
            continue;
        }

        const localPath = decodeURIComponent(destination.split('#')[0]);
        const resolved = path.resolve(path.dirname(file), localPath);

        if (!fs.existsSync(resolved)) {
            errors.push(`${relativeFile}: tautan lokal tidak ditemukan: ${destination}`);
        }
    }
}

if (errors.length) {
    console.error(errors.join('\n'));
    process.exit(1);
}

console.log(`OK: ${markdownFiles.length} file Markdown valid`);
