import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import { TableKit } from '@tiptap/extension-table';

const instances = new Map();

function normaliseUrl(url) {
    const value = (url || '').trim();

    if (!value) {
        return '';
    }

    if (/^(https?:\/\/|mailto:|tel:|\/|#)/i.test(value)) {
        return value;
    }

    return 'https://' + value;
}

function createEditor(root) {
    if (!root || root.dataset.swsRichTextReady === '1') {
        return;
    }

    const name = root.dataset.richTextName;
    const surface = root.querySelector('[data-editor-surface]');
    const source = root.querySelector('[data-editor-source]');
    const input = root.querySelector('[data-editor-input]');
    const toolbar = root.querySelector('[data-editor-toolbar]');
    const format = root.querySelector('[data-action="format"]');

    if (!name || !surface || !source || !input || !toolbar) {
        return;
    }

    let sourceMode = false;
    let fullscreen = false;

    const editor = new Editor({
        element: surface,
        content: input.value || '<p></p>',
        extensions: [
            StarterKit.configure({
                heading: {
                    levels: [1, 2, 3],
                },
                link: {
                    openOnClick: false,
                    autolink: true,
                    linkOnPaste: true,
                    HTMLAttributes: {
                        target: '_blank',
                        rel: 'noopener noreferrer',
                    },
                },
            }),
            Image.configure({
                allowBase64: false,
                HTMLAttributes: {
                    style: 'max-width:100%;height:auto;',
                },
            }),
            TableKit.configure({
                table: {
                    resizable: false,
                    HTMLAttributes: {
                        style: 'border:1px solid #333;border-collapse:collapse;',
                    },
                },
                tableHeader: {
                    HTMLAttributes: {
                        style: 'border:1px solid #333;padding:4px 8px;font-weight:bold;',
                    },
                },
                tableCell: {
                    HTMLAttributes: {
                        style: 'border:1px solid #333;padding:4px 8px;vertical-align:top;',
                    },
                },
            }),
        ],
        editorProps: {
            attributes: {
                spellcheck: 'true',
                'aria-label': 'Rich text editor',
            },
        },
        onCreate: () => {
            syncInput();
            syncToolbar();
        },
        onUpdate: () => {
            syncInput();
            syncToolbar();
        },
        onSelectionUpdate: () => {
            syncToolbar();
        },
        onTransaction: () => {
            syncToolbar();
        },
    });

    function currentHtml() {
        return sourceMode ? source.value : editor.getHTML();
    }

    function syncInput() {
        input.value = currentHtml();
        input.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function setSourceMode(enabled) {
        if (enabled === sourceMode) {
            return;
        }

        if (enabled) {
            source.value = editor.getHTML();
            sourceMode = true;
            root.classList.add('is-source-mode');
        } else {
            editor.commands.setContent(source.value || '<p></p>');
            sourceMode = false;
            root.classList.remove('is-source-mode');
            syncInput();
            editor.commands.focus();
        }

        syncToolbar();
    }

    function toggleFullscreen() {
        fullscreen = !fullscreen;
        root.classList.toggle('is-fullscreen', fullscreen);
        document.body.classList.toggle('sws-rte-fullscreen-open', fullscreen);

        const button = toolbar.querySelector('[data-action="fullscreen"]');
        if (button) {
            button.classList.toggle('is-active', fullscreen);
        }
    }

    function setLink() {
        const previous = editor.getAttributes('link').href || '';
        const entered = window.prompt('Link URL', previous || 'https://');

        if (entered === null) {
            return;
        }

        const href = normaliseUrl(entered);

        if (!href) {
            editor.chain().focus().extendMarkRange('link').unsetLink().run();
            return;
        }

        editor.chain().focus().extendMarkRange('link').setLink({ href }).run();
    }

    function addImage() {
        const entered = window.prompt('Image URL', 'https://');

        if (entered === null) {
            return;
        }

        const src = normaliseUrl(entered);

        if (src) {
            editor.chain().focus().setImage({ src }).run();
        }
    }

    function runAction(action) {
        if (action === 'source') {
            setSourceMode(!sourceMode);
            return;
        }

        if (action === 'fullscreen') {
            toggleFullscreen();
            return;
        }

        if (sourceMode) {
            return;
        }

        switch (action) {
            case 'undo':
                editor.chain().focus().undo().run();
                break;
            case 'redo':
                editor.chain().focus().redo().run();
                break;
            case 'bold':
                editor.chain().focus().toggleBold().run();
                break;
            case 'italic':
                editor.chain().focus().toggleItalic().run();
                break;
            case 'strike':
                editor.chain().focus().toggleStrike().run();
                break;
            case 'clear':
                editor.chain().focus().unsetAllMarks().clearNodes().run();
                break;
            case 'orderedList':
                editor.chain().focus().toggleOrderedList().run();
                break;
            case 'bulletList':
                editor.chain().focus().toggleBulletList().run();
                break;
            case 'outdent':
                editor.chain().focus().liftListItem('listItem').run();
                break;
            case 'indent':
                editor.chain().focus().sinkListItem('listItem').run();
                break;
            case 'blockquote':
                editor.chain().focus().toggleBlockquote().run();
                break;
            case 'link':
                setLink();
                break;
            case 'unlink':
                editor.chain().focus().extendMarkRange('link').unsetLink().run();
                break;
            case 'image':
                addImage();
                break;
            case 'table':
                editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run();
                break;
        }
    }

    function syncToolbar() {
        const buttons = toolbar.querySelectorAll('[data-action]');

        buttons.forEach((button) => {
            if (button.tagName === 'SELECT') {
                return;
            }

            const action = button.dataset.action;

            if (action !== 'source' && action !== 'fullscreen') {
                button.disabled = sourceMode;
            }

            let active = false;

            if (!sourceMode) {
                if (action === 'bold') active = editor.isActive('bold');
                if (action === 'italic') active = editor.isActive('italic');
                if (action === 'strike') active = editor.isActive('strike');
                if (action === 'orderedList') active = editor.isActive('orderedList');
                if (action === 'bulletList') active = editor.isActive('bulletList');
                if (action === 'blockquote') active = editor.isActive('blockquote');
                if (action === 'link') active = editor.isActive('link');
            }

            if (action === 'source') active = sourceMode;
            if (action === 'fullscreen') active = fullscreen;

            button.classList.toggle('is-active', active);
        });

        if (format) {
            format.disabled = sourceMode;

            if (editor.isActive('heading', { level: 1 })) {
                format.value = 'heading1';
            } else if (editor.isActive('heading', { level: 2 })) {
                format.value = 'heading2';
            } else if (editor.isActive('heading', { level: 3 })) {
                format.value = 'heading3';
            } else {
                format.value = 'paragraph';
            }
        }

        const outdent = toolbar.querySelector('[data-action="outdent"]');
        const indent = toolbar.querySelector('[data-action="indent"]');

        if (!sourceMode && outdent) {
            outdent.disabled = !editor.can().liftListItem('listItem');
        }

        if (!sourceMode && indent) {
            indent.disabled = !editor.can().sinkListItem('listItem');
        }
    }

    toolbar.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-action]');

        if (!button) {
            return;
        }

        event.preventDefault();
        runAction(button.dataset.action);
    });

    if (format) {
        format.addEventListener('change', () => {
            if (sourceMode) {
                return;
            }

            if (format.value === 'heading1') {
                editor.chain().focus().toggleHeading({ level: 1 }).run();
            } else if (format.value === 'heading2') {
                editor.chain().focus().toggleHeading({ level: 2 }).run();
            } else if (format.value === 'heading3') {
                editor.chain().focus().toggleHeading({ level: 3 }).run();
            } else {
                editor.chain().focus().setParagraph().run();
            }
        });
    }

    source.addEventListener('input', syncInput);

    const form = root.closest('form');
    if (form) {
        form.addEventListener('submit', () => {
            syncInput();
        });
    }

    const api = {
        editor,
        getHTML: currentHtml,
        sync: syncInput,
        focus: () => {
            if (!sourceMode) editor.commands.focus();
        },
        destroy: () => editor.destroy(),
    };

    root._swsRichText = api;
    root.dataset.swsRichTextReady = '1';
    instances.set(name, api);
}

function initEditors(scope = document) {
    scope.querySelectorAll('[data-sws-rich-text]').forEach(createEditor);
}

window.SwsRichText = {
    init: initEditors,
    get(name) {
        const instance = instances.get(name);
        return instance ? instance.getHTML() : '';
    },
    sync(name) {
        const instance = instances.get(name);
        if (instance) {
            instance.sync();
        }
    },
    focus(name) {
        const instance = instances.get(name);
        if (instance) {
            instance.focus();
        }
    },
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initEditors());
} else {
    initEditors();
}

document.addEventListener('livewire:navigated', () => initEditors());
