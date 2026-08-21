import { Editor, mergeAttributes } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import { TableKit } from '@tiptap/extension-table';
import Underline from '@tiptap/extension-underline';
import { TextStyleKit } from '@tiptap/extension-text-style';

const instances = new Map();
let activeEditorName = null;

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

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function escapeAttr(value) {
    return escapeHtml(value);
}

const SWSImage = Image.extend({
    addAttributes() {
        return {
            ...this.parent?.(),
            width: {
                default: '100%',
                parseHTML: element =>
                    element.getAttribute('data-sws-width') ||
                    element.style.width ||
                    '100%',
            },
            align: {
                default: 'left',
                parseHTML: element => {
                    const saved = element.getAttribute('data-sws-align');

                    if (['left', 'center', 'right', 'wrap-left', 'wrap-right'].includes(saved)) {
                        return saved;
                    }

                    if (element.style.float === 'left') {
                        return 'wrap-left';
                    }

                    if (element.style.float === 'right') {
                        return 'wrap-right';
                    }

                    return element.style.marginLeft === 'auto' && element.style.marginRight === 'auto'
                        ? 'center'
                        : element.style.marginLeft === 'auto'
                            ? 'right'
                            : 'left';
                },
            },
        };
    },

    renderHTML({ HTMLAttributes }) {
        const width = ['25%', '50%', '75%', '100%'].includes(HTMLAttributes.width)
            ? HTMLAttributes.width
            : '100%';

        const align = ['left', 'center', 'right', 'wrap-left', 'wrap-right'].includes(HTMLAttributes.align)
            ? HTMLAttributes.align
            : 'left';

        let style;

        if (align === 'wrap-left') {
            style = `display:block;float:left;width:${width};max-width:calc(100% - 15px);height:auto;margin:0 15px 10px 0;`;
        } else if (align === 'wrap-right') {
            style = `display:block;float:right;width:${width};max-width:calc(100% - 15px);height:auto;margin:0 0 10px 15px;`;
        } else {
            const margin =
                align === 'center'
                    ? '0 auto'
                    : align === 'right'
                        ? '0 0 0 auto'
                        : '0 auto 0 0';

            style = `display:block;float:none;width:${width};max-width:100%;height:auto;margin:${margin};`;
        }

        const attributes = { ...HTMLAttributes };
        delete attributes.width;
        delete attributes.align;

        return [
            'img',
            mergeAttributes(this.options.HTMLAttributes, attributes, {
                'data-sws-width': width,
                'data-sws-align': align,
                style,
            }),
        ];
    },
});

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
    const uploadInput = root.querySelector('[data-editor-upload]');
    const uploadStatus = root.querySelector('[data-editor-upload-status]');
    const imageTools = root.querySelector('[data-editor-image-tools]');
    const tableTools = root.querySelector('[data-editor-table-tools]');
    const uploadUrl = root.dataset.uploadUrl || '';

    if (!name || !surface || !source || !input || !toolbar) {
        return;
    }

    let sourceMode = false;
    let fullscreen = false;
    let selectedImagePos = null;
    let selectedTableAnchor = null;

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
            Underline,
            TextStyleKit,
            SWSImage.configure({
                allowBase64: false,
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
        onFocus: () => {
            activeEditorName = name;
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

    function insertImage(url, alt = '') {
        if (!url) return;

        activeEditorName = name;
        editor.chain().focus().setImage({
            src: url,
            alt,
            width: '100%',
            align: 'left',
        }).run();

        syncInput();
        syncToolbar();
    }

    function insertFileLink(url, label) {
        if (!url) return;

        activeEditorName = name;
        editor.chain().focus().insertContent(
            '<p><a href="' + escapeAttr(url) + '" target="_blank" rel="noopener noreferrer">' +
            escapeHtml(label || 'Open file') +
            '</a></p>'
        ).run();
        syncInput();
    }

    async function uploadFile(file) {
        if (!uploadUrl || !file) return;

        const button = toolbar.querySelector('[data-upload-trigger]');
        const oldHtml = button ? button.innerHTML : '';

        if (uploadStatus) {
            uploadStatus.classList.remove('is-error', 'is-success');
            uploadStatus.textContent = 'Uploading ' + file.name + '...';
        }

        if (button) {
            button.classList.add('sws-rte-uploading');
            button.innerHTML = '<i class="fa fa-spinner fa-pulse"></i>';
        }

        const csrf =
            document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
            document.querySelector('meta[name="token"]')?.getAttribute('value') ||
            '';

        const formData = new FormData();
        formData.append('singlefile', file);
        formData.append('editor', 'tiptap');

        try {
            const response = await fetch(uploadUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const data = await response.json().catch(() => null);

            if (!response.ok || !data || !data.url) {
                throw new Error(data?.message || 'Upload failed.');
            }

            if (data.is_image) {
                insertImage(data.url, data.name || file.name);
            } else {
                insertFileLink(data.url, 'Open PDF: ' + (data.name || file.name));
            }

            if (uploadStatus) {
                uploadStatus.classList.add('is-success');
                uploadStatus.textContent = data.is_image
                    ? 'Inserted ' + (data.name || file.name) + ' — click the image to resize or move it'
                    : 'Inserted ' + (data.name || file.name);
                window.setTimeout(() => {
                    uploadStatus.textContent = '';
                    uploadStatus.classList.remove('is-success');
                }, 3000);
            }

            root.dispatchEvent(new CustomEvent('sws-rich-text-uploaded', {
                bubbles: true,
                detail: data,
            }));
        } catch (error) {
            const message = error?.message || 'Upload failed. Please try again.';

            if (uploadStatus) {
                uploadStatus.classList.add('is-error');
                uploadStatus.textContent = message;
            }

            console.error('Tiptap upload failed:', error);
            window.alert(message);
        } finally {
            if (button) {
                button.classList.remove('sws-rte-uploading');
                button.innerHTML = oldHtml;
            }

            if (uploadInput) {
                uploadInput.value = '';
            }
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
            case 'underline':
                editor.chain().focus().toggleUnderline().run();
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
            case 'horizontalRule':
                editor.chain().focus().setHorizontalRule().run();
                break;
            case 'upload':
                activeEditorName = name;
                if (uploadInput) uploadInput.click();
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

    function positionImageTools() {
        if (!imageTools || selectedImagePos === null || !imageTools.classList.contains('is-visible')) {
            return;
        }

        const imageNode = editor.view.nodeDOM(selectedImagePos);

        if (!imageNode || !imageNode.getBoundingClientRect) {
            return;
        }

        const imageRect = imageNode.getBoundingClientRect();
        const toolsRect = imageTools.getBoundingClientRect();

        let top = imageRect.top - toolsRect.height - 10;
        let below = false;

        if (top < 8) {
            top = imageRect.bottom + 10;
            below = true;
        }

        let left = imageRect.left;
        const maxLeft = window.innerWidth - toolsRect.width - 8;

        left = Math.max(8, Math.min(left, maxLeft));

        imageTools.style.top = Math.max(8, top) + 'px';
        imageTools.style.left = left + 'px';
        imageTools.classList.toggle('is-below', below);
    }

    function syncImageTools() {
        if (!imageTools) {
            return;
        }

        const selection = editor.state.selection;
        const node = selection && selection.node;
        const selected = !!(node && node.type && node.type.name === 'image');

        if (!selected) {
            selectedImagePos = null;
            imageTools.classList.remove('is-visible', 'is-below');
            return;
        }

        selectedImagePos = selection.from;
        const attrs = node.attrs || {};
        const width = attrs.width || '100%';
        const align = attrs.align || 'left';

        imageTools.classList.add('is-visible');

        imageTools.querySelectorAll('[data-image-width]').forEach((button) => {
            button.classList.toggle('is-active', button.dataset.imageWidth === width);
        });

        imageTools.querySelectorAll('[data-image-align]').forEach((button) => {
            button.classList.toggle('is-active', button.dataset.imageAlign === align);
        });

        window.requestAnimationFrame(positionImageTools);
    }

    function getTableAnchorElement() {
        if (!tableTools || !editor.isActive('table')) {
            return null;
        }

        const selection = editor.state.selection;
        const node = selection && selection.node;

        if (node && node.type && node.type.name === 'image') {
            return null;
        }

        const domAtPos = editor.view.domAtPos(selection.from);
        let element = domAtPos.node;

        if (element && element.nodeType === Node.TEXT_NODE) {
            element = element.parentElement;
        } else if (element && element.nodeType !== Node.ELEMENT_NODE) {
            element = element.parentElement;
        }

        if (!element || !element.closest) {
            return null;
        }

        return element.closest('td, th') || element.closest('table');
    }

    function positionTableTools() {
        if (!tableTools || !selectedTableAnchor || !tableTools.classList.contains('is-visible')) {
            return;
        }

        if (!document.body.contains(selectedTableAnchor)) {
            selectedTableAnchor = getTableAnchorElement();

            if (!selectedTableAnchor) {
                tableTools.classList.remove('is-visible', 'is-below');
                return;
            }
        }

        const anchorRect = selectedTableAnchor.getBoundingClientRect();
        const toolsRect = tableTools.getBoundingClientRect();

        let top = anchorRect.top - toolsRect.height - 10;
        let below = false;

        if (top < 8) {
            top = anchorRect.bottom + 10;
            below = true;
        }

        let left = anchorRect.left;
        const maxLeft = window.innerWidth - toolsRect.width - 8;

        left = Math.max(8, Math.min(left, maxLeft));

        tableTools.style.top = Math.max(8, top) + 'px';
        tableTools.style.left = left + 'px';
        tableTools.classList.toggle('is-below', below);
    }

    function syncTableTools() {
        if (!tableTools) {
            return;
        }

        selectedTableAnchor = getTableAnchorElement();

        if (!selectedTableAnchor) {
            tableTools.classList.remove('is-visible', 'is-below');
            return;
        }

        tableTools.classList.add('is-visible');

        const can = editor.can();

        const commandChecks = {
            addRowBefore: () => can.addRowBefore(),
            addRowAfter: () => can.addRowAfter(),
            deleteRow: () => can.deleteRow(),
            addColumnBefore: () => can.addColumnBefore(),
            addColumnAfter: () => can.addColumnAfter(),
            deleteColumn: () => can.deleteColumn(),
            deleteTable: () => can.deleteTable(),
        };

        tableTools.querySelectorAll('[data-table-action]').forEach((button) => {
            const check = commandChecks[button.dataset.tableAction];
            button.disabled = check ? !check() : false;
        });

        window.requestAnimationFrame(positionTableTools);
    }

    function syncToolbar() {
        syncImageTools();
        syncTableTools();

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
                if (action === 'underline') active = editor.isActive('underline');
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

    toolbar.querySelectorAll('input[type="color"][data-action]').forEach((picker) => {
        picker.addEventListener('input', () => {
            if (sourceMode) return;

            if (picker.dataset.action === 'textColor') {
                editor.chain().focus().setColor(picker.value).run();
            } else if (picker.dataset.action === 'backgroundColor') {
                editor.chain().focus().setBackgroundColor(picker.value).run();
            }
        });
    });

    if (imageTools) {
        imageTools.addEventListener('mousedown', (event) => {
            event.preventDefault();
        });

        imageTools.addEventListener('click', (event) => {
            const widthButton = event.target.closest('[data-image-width]');
            const alignButton = event.target.closest('[data-image-align]');

            if (selectedImagePos === null) {
                return;
            }

            if (widthButton) {
                event.preventDefault();

                const attrs = editor.state.doc.nodeAt(selectedImagePos)?.attrs || {};
                const updates = {
                    width: widthButton.dataset.imageWidth,
                };

                if (
                    widthButton.dataset.imageWidth === '100%' &&
                    ['wrap-left', 'wrap-right'].includes(attrs.align)
                ) {
                    updates.align = 'left';
                }

                editor.chain().setNodeSelection(selectedImagePos).updateAttributes('image', updates).run();
                syncInput();
                syncToolbar();
                return;
            }

            if (alignButton) {
                event.preventDefault();

                const attrs = editor.state.doc.nodeAt(selectedImagePos)?.attrs || {};
                const updates = {
                    align: alignButton.dataset.imageAlign,
                };

                if (
                    ['wrap-left', 'wrap-right'].includes(alignButton.dataset.imageAlign) &&
                    (attrs.width || '100%') === '100%'
                ) {
                    updates.width = '50%';
                }

                editor.chain().setNodeSelection(selectedImagePos).updateAttributes('image', updates).run();
                syncInput();
                syncToolbar();
            }
        });

        window.addEventListener('scroll', positionImageTools, true);
        window.addEventListener('resize', positionImageTools);
    }

    if (tableTools) {
        tableTools.addEventListener('mousedown', (event) => {
            event.preventDefault();
        });

        tableTools.addEventListener('click', (event) => {
            const button = event.target.closest('[data-table-action]');

            if (!button || button.disabled) {
                return;
            }

            event.preventDefault();

            const action = button.dataset.tableAction;
            let command = null;

            if (action === 'addRowBefore') command = () => editor.chain().focus().addRowBefore().run();
            if (action === 'addRowAfter') command = () => editor.chain().focus().addRowAfter().run();
            if (action === 'deleteRow') command = () => editor.chain().focus().deleteRow().run();
            if (action === 'addColumnBefore') command = () => editor.chain().focus().addColumnBefore().run();
            if (action === 'addColumnAfter') command = () => editor.chain().focus().addColumnAfter().run();
            if (action === 'deleteColumn') command = () => editor.chain().focus().deleteColumn().run();
            if (action === 'deleteTable') command = () => editor.chain().focus().deleteTable().run();

            if (command) {
                command();
                syncInput();
                syncToolbar();
            }
        });

        window.addEventListener('scroll', positionTableTools, true);
        window.addEventListener('resize', positionTableTools);
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
            activeEditorName = name;
            if (!sourceMode) editor.commands.focus();
        },
        insertImage,
        insertFileLink,
        uploadFile,
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
    activeName() {
        return activeEditorName;
    },
    insertImage(name, url, alt = '') {
        const instance = instances.get(name || activeEditorName);
        if (!instance) return false;
        instance.insertImage(url, alt);
        return true;
    },
    insertFileLink(name, url, label = 'Open file') {
        const instance = instances.get(name || activeEditorName);
        if (!instance) return false;
        instance.insertFileLink(url, label);
        return true;
    },
    uploadSelected(name, input) {
        const instance = instances.get(name);

        if (!instance) {
            window.alert('The rich text editor upload handler is not ready. Please hard refresh the page.');
            return false;
        }

        const file = input && input.files && input.files[0];

        if (!file) {
            return false;
        }

        activeEditorName = name;
        instance.uploadFile(file);
        return true;
    },
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initEditors());
} else {
    initEditors();
}

document.addEventListener('livewire:navigated', () => initEditors());
