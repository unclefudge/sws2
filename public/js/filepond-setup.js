//$.ajaxSetup({
//    header: $('meta[name="_token"]').attr('content')
//})

//var headers = {
//    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
//}
// register the plugins with FilePond
FilePond.registerPlugin(
    //FilePondPluginImageCrop,
    //FilePondPluginImagePreview, // show preview of image within the drag & drop box
    //FilePondPluginImageTransform,
    //FilePondPluginFileValidateType,
    FilePondPluginImageResize
);

// Get a reference to the file input element
const inputElement = document.querySelector('input[type="file"]'); // single Filepond instance

// Create a FilePond instance
const pond = FilePond.create(inputElement, {
    acceptedFileTypes: ['image/png'],
    imageResizeTargetWidth: 100,
    imageResizeMode: 'contain', // ensure resized image isn't displayed any larger than TargetWidth
    /*imageTransformVariants: {
        thumb_small_: transforms => {
            transforms.resize.size.width = 64;
            return transforms;
        },
        thumb_medium_: transforms => {
            //transforms.resize.size.width = 512;
            transforms.resize.size.height = 100;
            transforms.crop.aspectRatio = .5;   // this will be a landscape crop

            return transforms;
        },
    },*/
    onaddfile: (err, fileItem) => {
        console.log(err, fileItem.getMetadata('resize'));
    },

    // alter the output property
    onpreparefile: (fileItem, output) => {
        const img = new Image();
        img.src = URL.createObjectURL(output);
        console.log(img);
        document.body.appendChild(img);
    }
});

FilePond.setOptions({
    server: {
        url: '/form/upload',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="token"]').content
        }
    }
});
