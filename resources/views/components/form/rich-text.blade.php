@props([
    'name',
    'value' => '',
    'id' => null,
    'minHeight' => 360,
    'variant' => 'basic',
    'uploadUrl' => null,
    'uploadAccept' => 'image/*,application/pdf',
])

@php
    $editorId = $id ?: 'rich-text-' . \Illuminate\Support\Str::slug($name) . '-' . substr(md5($name), 0, 6);
@endphp

@once
    <style>
        .sws-rte {
            --sws-rte-min-height:360px;
            border:1px solid #cfd5da;
            background:#fff;
        }

        .sws-rte-toolbar {
            display:flex;
            flex-wrap:wrap;
            align-items:center;
            gap:4px;
            padding:7px 8px;
            border-bottom:1px solid #d9dfe4;
            background:#f7f8fa;
        }

        .sws-rte-group {
            display:inline-flex;
            align-items:center;
            gap:2px;
            padding-right:7px;
            margin-right:3px;
            border-right:1px solid #d8dde2;
        }

        .sws-rte-group:last-child {
            border-right:0;
            padding-right:0;
            margin-right:0;
        }

        .sws-rte-btn {
            min-width:32px;
            height:32px;
            padding:4px 8px;
            border:1px solid transparent;
            border-radius:3px;
            background:transparent;
            color:#4f5b65;
            line-height:22px;
            text-align:center;
        }

        .sws-rte-btn:hover,
        .sws-rte-btn:focus {
            border-color:#cdd5dc;
            background:#e9edf0;
            color:#2f3941;
            outline:0;
        }

        .sws-rte-btn.is-active {
            border-color:#9fcbd8;
            background:#dff3f7;
            color:#2387a1;
        }

        .sws-rte-btn:disabled,
        .sws-rte-format:disabled {
            opacity:.4;
            cursor:not-allowed;
        }

        .sws-rte-format {
            height:32px;
            min-width:125px;
            padding:4px 28px 4px 8px;
            border:1px solid #d1d7dc;
            background:#fff;
        }

        .sws-rte-editor {
            background:#fff;
        }

        .sws-rte-editor .ProseMirror {
            display:flow-root;
            min-height:var(--sws-rte-min-height);
            padding:18px 20px;
            color:#3f4a53;
            font-size:15px;
            line-height:1.55;
            outline:0;
        }

        .sws-rte-editor .ProseMirror p {
            margin:0 0 12px;
        }

        .sws-rte-editor .ProseMirror p:last-child {
            margin-bottom:0;
        }

        .sws-rte-editor .ProseMirror blockquote {
            margin:10px 0;
            padding:6px 14px;
            border-left:3px solid #cbd3d9;
            color:#65717b;
        }

        .sws-rte-editor .ProseMirror table {
            border-collapse:collapse;
            margin:12px 0;
        }

        .sws-rte-editor .ProseMirror th,
        .sws-rte-editor .ProseMirror td {
            min-width:80px;
            padding:6px 8px;
            border:1px solid #aeb7bf;
            vertical-align:top;
        }

        .sws-rte-editor .ProseMirror th {
            background:#f1f3f5;
            font-weight:600;
        }

        .sws-rte-editor .ProseMirror img {
            max-width:100%;
            height:auto;
        }

        .sws-rte-source {
            display:none;
            width:100%;
            min-height:var(--sws-rte-min-height);
            padding:14px 16px;
            border:0;
            resize:vertical;
            background:#fbfcfd;
            color:#39444d;
            font-family:Menlo, Monaco, Consolas, "Courier New", monospace;
            font-size:13px;
            line-height:1.5;
            outline:0;
        }

        .sws-rte.is-source-mode .sws-rte-editor {
            display:none;
        }

        .sws-rte.is-source-mode .sws-rte-source {
            display:block;
        }

        .sws-rte.is-fullscreen {
            position:fixed;
            inset:10px;
            z-index:10080;
            display:flex;
            flex-direction:column;
            border:1px solid #aeb7bf;
            box-shadow:0 8px 35px rgba(0,0,0,.25);
        }

        .sws-rte.is-fullscreen .sws-rte-editor,
        .sws-rte.is-fullscreen .sws-rte-source {
            flex:1;
            overflow:auto;
        }

        .sws-rte.is-fullscreen .sws-rte-editor .ProseMirror {
            min-height:100%;
        }

        body.sws-rte-fullscreen-open {
            overflow:hidden;
        }

        .sws-rte-upload-input {
            position:absolute !important;
            width:1px !important;
            height:1px !important;
            padding:0 !important;
            margin:-1px !important;
            overflow:hidden !important;
            clip:rect(0, 0, 0, 0) !important;
            white-space:nowrap !important;
            border:0 !important;
            opacity:0 !important;
        }

        .sws-rte-upload-label {
            display:inline-flex;
            align-items:center;
            gap:5px;
            width:auto;
            min-width:72px;
            cursor:pointer;
            margin:0;
            padding:4px 9px !important;
            font-weight:600;
        }

        .sws-rte-upload-status {
            display:inline-block;
            max-width:180px;
            margin-left:4px;
            color:#6b7680;
            font-size:11px;
            line-height:30px;
            vertical-align:middle;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }

        .sws-rte-upload-status.is-error {
            color:#e7505a;
        }

        .sws-rte-upload-status.is-success {
            color:#26a69a;
        }

        .sws-rte-image-tools {
            display:none;
            position:fixed;
            z-index:10095;
            align-items:center;
            flex-wrap:wrap;
            gap:5px;
            max-width:calc(100vw - 20px);
            padding:8px 10px;
            border:1px solid #cfd6dc;
            border-radius:5px;
            background:#fff;
            box-shadow:0 4px 18px rgba(0,0,0,.18);
            color:#5d6871;
            font-size:12px;
        }

        .sws-rte-image-tools.is-visible {
            display:flex;
        }

        .sws-rte-image-tools:after {
            content:"";
            position:absolute;
            left:24px;
            bottom:-7px;
            width:12px;
            height:12px;
            border-right:1px solid #cfd6dc;
            border-bottom:1px solid #cfd6dc;
            background:#fff;
            transform:rotate(45deg);
        }

        .sws-rte-image-tools.is-below:after {
            top:-7px;
            bottom:auto;
            border:0;
            border-left:1px solid #cfd6dc;
            border-top:1px solid #cfd6dc;
        }

        .sws-rte-image-tools-label {
            margin-right:3px;
            font-weight:700;
        }

        .sws-rte-image-tools-group-label {
            margin-left:4px;
            color:#7a858e;
            font-weight:600;
        }

        .sws-rte-image-tools-separator {
            width:1px;
            height:24px;
            margin:0 4px;
            background:#d8dde2;
        }

        .sws-rte-image-btn {
            min-width:52px;
            height:30px;
            padding:3px 8px;
            border:1px solid #d2d8dd;
            border-radius:3px;
            background:#fff;
            color:#53606a;
            font-size:12px;
            line-height:20px;
        }

        .sws-rte-image-btn:hover,
        .sws-rte-image-btn:focus {
            background:#eef2f4;
            outline:0;
        }

        .sws-rte-image-btn.is-active {
            border-color:#8bc4d3;
            background:#dff3f7;
            color:#2387a1;
        }

        .sws-rte-editor .ProseMirror img.ProseMirror-selectednode {
            outline:3px solid #36c6d3;
            outline-offset:2px;
        }

        .sws-rte-image-hint {
            color:#8b969f;
            font-size:11px;
        }

        .sws-rte-table-tools {
            display:none;
            position:fixed;
            z-index:10095;
            align-items:center;
            flex-wrap:wrap;
            gap:5px;
            max-width:calc(100vw - 20px);
            padding:8px 10px;
            border:1px solid #cfd6dc;
            border-radius:5px;
            background:#fff;
            box-shadow:0 4px 18px rgba(0,0,0,.18);
            color:#5d6871;
            font-size:12px;
        }

        .sws-rte-table-tools.is-visible {
            display:flex;
        }

        .sws-rte-table-tools:after {
            content:"";
            position:absolute;
            left:24px;
            bottom:-7px;
            width:12px;
            height:12px;
            border-right:1px solid #cfd6dc;
            border-bottom:1px solid #cfd6dc;
            background:#fff;
            transform:rotate(45deg);
        }

        .sws-rte-table-tools.is-below:after {
            top:-7px;
            bottom:auto;
            border:0;
            border-left:1px solid #cfd6dc;
            border-top:1px solid #cfd6dc;
        }

        .sws-rte-table-tools-label {
            margin-right:3px;
            font-weight:700;
        }

        .sws-rte-table-tools-group-label {
            margin-left:4px;
            color:#7a858e;
            font-weight:600;
        }

        .sws-rte-table-tools-separator {
            width:1px;
            height:24px;
            margin:0 4px;
            background:#d8dde2;
        }

        .sws-rte-table-btn {
            min-width:52px;
            height:30px;
            padding:3px 8px;
            border:1px solid #d2d8dd;
            border-radius:3px;
            background:#fff;
            color:#53606a;
            font-size:12px;
            line-height:20px;
        }

        .sws-rte-table-btn:hover,
        .sws-rte-table-btn:focus {
            background:#eef2f4;
            outline:0;
        }

        .sws-rte-table-btn.is-danger {
            border-color:#efc1c5;
            color:#c94b55;
        }

        .sws-rte-table-btn.is-danger:hover,
        .sws-rte-table-btn.is-danger:focus {
            background:#fff0f1;
        }

        .sws-rte-table-btn:disabled {
            opacity:.4;
            cursor:not-allowed;
        }

        .sws-rte-table-hint {
            color:#8b969f;
            font-size:11px;
        }

        .sws-rte-color {
            width:30px;
            height:30px;
            padding:2px;
            border:1px solid #d1d7dc;
            border-radius:3px;
            background:#fff;
            cursor:pointer;
        }

        .sws-rte-uploading {
            pointer-events:none;
            opacity:.55;
        }

        @media (max-width:767px) {
            .sws-rte-toolbar {
                gap:2px;
                padding:5px;
            }

            .sws-rte-group {
                padding-right:4px;
                margin-right:1px;
            }

            .sws-rte-btn {
                min-width:30px;
                padding:4px 6px;
            }

            .sws-rte-format {
                min-width:105px;
            }
        }
    </style>
@endonce

<div id="{{ $editorId }}" class="sws-rte" data-sws-rich-text data-rich-text-name="{{ $name }}" data-editor-variant="{{ $variant }}" @if($uploadUrl) data-upload-url="{{ $uploadUrl }}" @endif style="--sws-rte-min-height:{{ (int)$minHeight }}px">
    <div class="sws-rte-toolbar" data-editor-toolbar>
        <span class="sws-rte-group">
            <button type="button" class="sws-rte-btn" data-action="undo" title="Undo"><i class="fa fa-undo"></i></button>
            <button type="button" class="sws-rte-btn" data-action="redo" title="Redo"><i class="fa fa-repeat"></i></button>
        </span>

        <span class="sws-rte-group">
            <select class="sws-rte-format" data-action="format" title="Format">
                <option value="paragraph">Paragraph</option>
                <option value="heading1">Heading 1</option>
                <option value="heading2">Heading 2</option>
                <option value="heading3">Heading 3</option>
            </select>
        </span>

        <span class="sws-rte-group">
            <button type="button" class="sws-rte-btn" data-action="bold" title="Bold"><i class="fa fa-bold"></i></button>
            <button type="button" class="sws-rte-btn" data-action="italic" title="Italic"><i class="fa fa-italic"></i></button>
            @if ($variant === 'document')
                <button type="button" class="sws-rte-btn" data-action="underline" title="Underline"><i class="fa fa-underline"></i></button>
            @endif
            <button type="button" class="sws-rte-btn" data-action="strike" title="Strike"><i class="fa fa-strikethrough"></i></button>
            <button type="button" class="sws-rte-btn" data-action="clear" title="Clear formatting"><i class="fa fa-eraser"></i></button>
        </span>

        @if ($variant === 'document')
            <span class="sws-rte-group">
                <input type="color" class="sws-rte-color" value="#444444" data-action="textColor" title="Text colour">
                <input type="color" class="sws-rte-color" value="#fff2a8" data-action="backgroundColor" title="Background colour">
            </span>
        @endif

        <span class="sws-rte-group">
            <button type="button" class="sws-rte-btn" data-action="orderedList" title="Numbered list"><i class="fa fa-list-ol"></i></button>
            <button type="button" class="sws-rte-btn" data-action="bulletList" title="Bullet list"><i class="fa fa-list-ul"></i></button>
            <button type="button" class="sws-rte-btn" data-action="outdent" title="Outdent"><i class="fa fa-outdent"></i></button>
            <button type="button" class="sws-rte-btn" data-action="indent" title="Indent"><i class="fa fa-indent"></i></button>
            <button type="button" class="sws-rte-btn" data-action="blockquote" title="Quote"><i class="fa fa-quote-right"></i></button>
            @if ($variant === 'document')
                <button type="button" class="sws-rte-btn" data-action="horizontalRule" title="Horizontal line"><i class="fa fa-minus"></i></button>
            @endif
        </span>

        <span class="sws-rte-group">
            <button type="button" class="sws-rte-btn" data-action="link" title="Add/edit link"><i class="fa fa-link"></i></button>
            <button type="button" class="sws-rte-btn" data-action="unlink" title="Remove link"><i class="fa fa-chain-broken"></i></button>
            @if ($uploadUrl)
                <label for="{{ $editorId }}-upload" class="sws-rte-btn sws-rte-upload-label" data-upload-trigger title="Upload image or PDF"><i class="fa fa-upload"></i> Upload</label>
                <span class="sws-rte-upload-status" data-editor-upload-status></span>
            @endif
            <button type="button" class="sws-rte-btn" data-action="image" title="Insert image from URL"><i class="fa fa-image"></i></button>
            <button type="button" class="sws-rte-btn" data-action="table" title="Insert 3 x 3 table"><i class="fa fa-table"></i></button>
        </span>

        <span class="sws-rte-group">
            <button type="button" class="sws-rte-btn" data-action="fullscreen" title="Fullscreen"><i class="fa fa-arrows-alt"></i></button>
            <button type="button" class="sws-rte-btn" data-action="source" title="HTML source"><i class="fa fa-code"></i> Source</button>
        </span>
    </div>

    @if ($variant === 'document')
        <div class="sws-rte-image-tools" data-editor-image-tools>
            <span class="sws-rte-image-tools-label"><i class="fa fa-image"></i> Edit image</span>

            <span class="sws-rte-image-tools-group-label">Size:</span>
            <button type="button" class="sws-rte-image-btn" data-image-width="25%" title="25% width">Small</button>
            <button type="button" class="sws-rte-image-btn" data-image-width="50%" title="50% width">Medium</button>
            <button type="button" class="sws-rte-image-btn" data-image-width="75%" title="75% width">Large</button>
            <button type="button" class="sws-rte-image-btn" data-image-width="100%" title="100% width">Full width</button>

            <span class="sws-rte-image-tools-separator"></span>

            <span class="sws-rte-image-tools-group-label">Position:</span>
            <button type="button" class="sws-rte-image-btn" data-image-align="left"><i class="fa fa-align-left"></i> Left</button>
            <button type="button" class="sws-rte-image-btn" data-image-align="center"><i class="fa fa-align-center"></i> Centre</button>
            <button type="button" class="sws-rte-image-btn" data-image-align="right"><i class="fa fa-align-right"></i> Right</button>

            <span class="sws-rte-image-tools-separator"></span>

            <span class="sws-rte-image-tools-group-label">Text beside:</span>
            <button type="button" class="sws-rte-image-btn" data-image-align="wrap-left" title="Image on the left with text beside it"><i class="fa fa-picture-o"></i> Image left</button>
            <button type="button" class="sws-rte-image-btn" data-image-align="wrap-right" title="Image on the right with text beside it"><i class="fa fa-picture-o"></i> Image right</button>

            <span class="sws-rte-image-hint">Click another image to edit it.</span>
        </div>

        <div class="sws-rte-table-tools" data-editor-table-tools>
            <span class="sws-rte-table-tools-label"><i class="fa fa-table"></i> Edit table</span>

            <span class="sws-rte-table-tools-group-label">Rows:</span>
            <button type="button" class="sws-rte-table-btn" data-table-action="addRowBefore"><i class="fa fa-plus"></i> Above</button>
            <button type="button" class="sws-rte-table-btn" data-table-action="addRowAfter"><i class="fa fa-plus"></i> Below</button>
            <button type="button" class="sws-rte-table-btn is-danger" data-table-action="deleteRow"><i class="fa fa-trash"></i> Row</button>

            <span class="sws-rte-table-tools-separator"></span>

            <span class="sws-rte-table-tools-group-label">Columns:</span>
            <button type="button" class="sws-rte-table-btn" data-table-action="addColumnBefore"><i class="fa fa-plus"></i> Left</button>
            <button type="button" class="sws-rte-table-btn" data-table-action="addColumnAfter"><i class="fa fa-plus"></i> Right</button>
            <button type="button" class="sws-rte-table-btn is-danger" data-table-action="deleteColumn"><i class="fa fa-trash"></i> Column</button>

            <span class="sws-rte-table-tools-separator"></span>

            <button type="button" class="sws-rte-table-btn is-danger" data-table-action="deleteTable"><i class="fa fa-trash"></i> Delete table</button>

            <span class="sws-rte-table-hint">Click a table cell to edit its row or column.</span>
        </div>
    @endif

    <div class="sws-rte-editor" data-editor-surface></div>
    <textarea class="sws-rte-source" data-editor-source spellcheck="false"></textarea>
    @if ($uploadUrl)
        <input id="{{ $editorId }}-upload" type="file" class="sws-rte-upload-input" data-editor-upload accept="{{ $uploadAccept }}" onchange="window.SwsRichText ? window.SwsRichText.uploadSelected(this.closest('[data-sws-rich-text]').dataset.richTextName, this) : alert('Rich text upload code is not loaded. Please hard refresh the page.')">
    @endif
    <textarea name="{{ $name }}" class="sws-rte-input" data-editor-input style="display:none">{{ old($name, $value) }}</textarea>
</div>
