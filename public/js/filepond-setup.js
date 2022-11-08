//$.ajaxSetup({
//    header: $('meta[name="_token"]').attr('content')
//})

//var headers = {
//    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
//}
// register the plugins with FilePond
FilePond.registerPlugin(
    FilePondPluginImageCrop,
    FilePondPluginImagePreview,
    FilePondPluginImageResize,
    FilePondPluginImageTransform
);

// Get a reference to the file input element
const inputElement = document.querySelector('input[type="file"]');

// Create a FilePond instance
const pond = FilePond.create(inputElement, {
    imageResizeTargetWidth: 256,
    imageResizeMode: 'contain',
    imageTransformVariants: {
        thumb_medium_: transforms => {
            //transforms.resize.size.width = 512;
            transforms.resize.size.height = 100;
            transforms.crop.aspectRatio = .5;   // this will be a landscape crop

            return transforms;
        },
        thumb_small_: transforms => {
            transforms.resize.size.width = 64;
            return transforms;
        }
    },
    onaddfile: (err, fileItem) => {
        console.log(err, fileItem.getMetadata('resize'));
    },

    // alter the output property
    onpreparefile: (fileItem, outputFiles) => {
        // loop over the outputFiles array
        outputFiles.forEach(output => {
            const img = new Image();

            // output now is an object containing a `name` and a `file` property, we only need the `file`
            img.src = URL.createObjectURL(output.file);

            document.body.appendChild(img);
        })
    }
});

var headers = {
    'X-CSRF-TOKEN': $('meta[name="token"]').attr('content')
}
console.log(headers);

FilePond.setOptions({
    server: {
        url: '/form/upload',
        headers: {
            'X-CSRF-TOKEN':  document.querySelector('meta[name="token"]').content
        }
    }
});
