// register the plugins with FilePond
FilePond.registerPlugin(
    //FilePondPluginImageCrop,
    //FilePondPluginImagePreview, // show preview of image within the drag & drop box
    //FilePondPluginImageTransform,
    FilePondPluginImageResize
);

// Get a reference to the file input element
const inputElements = document.querySelectorAll('input.my-filepond'); // multiple Filepond instances

// loop over filepond inputElements
Array.from(inputElements).forEach(inputElement => {

    const pond = FilePond.create(inputElement, {
        url: '/form/upload',
        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="token"]').content},
        imageResizeTargetWidth: 100,
        imageResizeMode: 'contain', // ensure resized image isn't displayed any larger than TargetWidth

        // display added file info to console for debuging
        onaddfile: (err, fileItem) => {
            console.log('File added');
            console.log(err, fileItem.getMetadata('resize'));
            console.log(inputElement);
            inputElement.style.height = '500';
            console.log(inputElement);
            console.log(inputElement.name);
            //console.log(inputElement.element.style);
        },

        // alter the output property
        onpreparefile: (fileItem, output) => {
            const img = new Image();
            img.src = URL.createObjectURL(output);
            //console.log('File prepared');
            //console.log(img);
            //console.log(inputElement);
            document.body.appendChild(img);
        },
    });

    //console.log(pond.element.style);
    //pond.element.style.height= '333';
});


