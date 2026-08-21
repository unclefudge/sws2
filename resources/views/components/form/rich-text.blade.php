@props([
    'name',
    'value' => '',
    'id' => null,
    'minHeight' => 360,
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

<div id="{{ $editorId }}" class="sws-rte" data-sws-rich-text data-rich-text-name="{{ $name }}" style="--sws-rte-min-height:{{ (int)$minHeight }}px">
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
            <button type="button" class="sws-rte-btn" data-action="strike" title="Strike"><i class="fa fa-strikethrough"></i></button>
            <button type="button" class="sws-rte-btn" data-action="clear" title="Clear formatting"><i class="fa fa-eraser"></i></button>
        </span>

        <span class="sws-rte-group">
            <button type="button" class="sws-rte-btn" data-action="orderedList" title="Numbered list"><i class="fa fa-list-ol"></i></button>
            <button type="button" class="sws-rte-btn" data-action="bulletList" title="Bullet list"><i class="fa fa-list-ul"></i></button>
            <button type="button" class="sws-rte-btn" data-action="outdent" title="Outdent"><i class="fa fa-outdent"></i></button>
            <button type="button" class="sws-rte-btn" data-action="indent" title="Indent"><i class="fa fa-indent"></i></button>
            <button type="button" class="sws-rte-btn" data-action="blockquote" title="Quote"><i class="fa fa-quote-right"></i></button>
        </span>

        <span class="sws-rte-group">
            <button type="button" class="sws-rte-btn" data-action="link" title="Add/edit link"><i class="fa fa-link"></i></button>
            <button type="button" class="sws-rte-btn" data-action="unlink" title="Remove link"><i class="fa fa-chain-broken"></i></button>
            <button type="button" class="sws-rte-btn" data-action="image" title="Insert image from URL"><i class="fa fa-image"></i></button>
            <button type="button" class="sws-rte-btn" data-action="table" title="Insert table"><i class="fa fa-table"></i></button>
        </span>

        <span class="sws-rte-group">
            <button type="button" class="sws-rte-btn" data-action="fullscreen" title="Fullscreen"><i class="fa fa-arrows-alt"></i></button>
            <button type="button" class="sws-rte-btn" data-action="source" title="HTML source"><i class="fa fa-code"></i> Source</button>
        </span>
    </div>

    <div class="sws-rte-editor" data-editor-surface></div>
    <textarea class="sws-rte-source" data-editor-source spellcheck="false"></textarea>
    <textarea name="{{ $name }}" class="sws-rte-input" data-editor-input style="display:none">{{ old($name, $value) }}</textarea>
</div>
