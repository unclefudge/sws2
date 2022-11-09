<!DOCTYPE html>
<html>
<head>
    <meta charset=utf-8 />
    <title>Filepond</title>
    <meta id="token" name="token" content="{{ csrf_token() }}"/>

    {{-- Filepond --}}
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" type="text/css"/>
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">
</head>
<body>
<div><h1>Filepond</h1></div>
<input type="file" class="my-pond" name="filepond-file" multiple/>
<div>
    <style>
        /*.filepond--item {
            width: calc(50% - 0.5em);
        }*/
        /*.filepond--hopper {
            height: 250px;
        }*/
        /*.filepond--root {
            min-height: 200px !important;
        }*/
    </style>
    <h4>Gallery</h4>
    <div id="gallery"></div>
</div>
</body>
</html>

<!-- Filepond -->
<script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-resize/dist/filepond-plugin-image-resize.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-transform/dist/filepond-plugin-image-transform.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-crop/dist/filepond-plugin-image-crop.js"></script>
<script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>

<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>


<script src="/js/filepond-setup.js"></script>