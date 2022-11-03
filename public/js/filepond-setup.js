// register the plugins with FilePond
FilePond.registerPlugin(
    FilePondPluginImagePreview,
    FilePondPluginImageResize,
    FilePondPluginImageTransform
);

// Get a reference to the file input element
const inputElement = document.querySelector('input[type="file"]');

// Create a FilePond instance
const pond = FilePond.create(inputElement,
    {
        imageResizeTargetWidth: 256,

        // add onaddfile callback
        onaddfile: (err, fileItem) => {
            console.log(err, fileItem.getMetadata('resize'));
        },

        // add fpr displaying the image on the screen
        onpreparefile: (fileItem, output) => {
            // create a new image object
            const img = new Image();

            // set the image source to the output of the Image Transform plugin
            img.src = URL.createObjectURL(output);

            // add it to the DOM so we can see the result
            document.body.appendChild(img);
        }
    });
