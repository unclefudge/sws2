// register the plugins with FilePond
FilePond.registerPlugin(
    //FilePondPluginImageCrop,
    //FilePondPluginImagePreview, // show preview of image within the drag & drop box
    //FilePondPluginImageTransform,
    FilePondPluginImageResize,
    FilePondPluginFileValidateType,
    FilePondPluginFileValidateSize
);

// Get a reference to the file input element
const inputElements = document.querySelectorAll('input.my-filepond'); // multiple Filepond instances

// loop over filepond inputElements
Array.from(inputElements).forEach(inputElement => {

    const pond = FilePond.create(inputElement, {
        server: {
            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="token"]').content},
            //process: '/site/inspection/upload',
            process: '/file/upload',
            revert: null, // remove file from Filepond upload list
            restore: null,
            fetch: null,  // used to load files on server
            load: null,
        },

        labelIdle: `<span class="btn btn-primary filepond--label-action">Add Media</span> &nbsp; or Drag & Drop your picture`,
        //acceptedFileTypes: ['image/png', 'image/jpeg', 'image/gif'],
        allowFileTypeValidation: true,
        maxFileSize: '5MB',
        imagePreviewHeight: 170,
        imageResizeTargetWidth: 100,
        imageResizeMode: 'contain', // ensure resized image isn't displayed any larger than TargetWidth

        // display added file info to console for debuging
        onaddfile: (err, fileItem) => {
            console.log('File added');
            //console.log(fileItem);
            console.log(fileItem.fileType);
            //console.log(fileItem.getMetadata('resize'));
            //console.log(inputElement);
            //console.log(inputElement.name);
            //console.log(inputElement.element.style);
        },

        // alter the output property
        onpreparefile: (fileItem, output) => {
            console.log('File prepared');
            console.log('fileType:' + fileItem.fileType);
            var imageTypes = ['image/png', 'image/jpeg', 'image/gif'];

            if (imageTypes.includes(fileItem.fileType)) {

                // Create thumbnail of image to display in gallery
                //  - but hide it with display:none until pfile processed successfully
                var question = inputElement.name.split('-media[')[0];
                const img = new Image();
                img.src = URL.createObjectURL(output);
                img.id = question + '-photo-' + fileItem.id; //URL.createObjectURL(output);
                img.width = 100;
                img.style = 'margin-right: 20px; display:none';

                console.log('Ele:' + inputElement);

                var gallery = document.getElementById(question + '-gallery');
                gallery.appendChild(img);

                var thumbnail = document.getElementById(img.id);
                thumbnail.style.opacity = '0.5';
                console.log(thumbnail);
            }
        },

        onprocessfile: (err, fileItem) => {
            console.log('File processing:' + err);
            console.log(fileItem);
            var imageTypes = ['image/png', 'image/jpeg', 'image/gif'];

            // Reveal newly uploaded thumbnail image
            if (!err && imageTypes.includes(fileItem.fileType)) {
                var question = inputElement.name.split('-media[')[0];
                var thumbnail = document.getElementById(question + '-photo-' + fileItem.id);
                thumbnail.style.display = 'inline';
            }
        },

        // remove file
        // alter the output property
        onremovefile: (err, fileItem) => {
            console.log('Remove File:' + err);
            var question = inputElement.name.split('-media[')[0];
            var thumbnail = document.getElementById(question + '-photo-' + fileItem.id);
            //console.log(fileItem);
            //console.log(thumbnail);
            thumbnail.style.display = 'none';
            //thumbnail.setAttribute("style","display:none");

        },
    });

    //console.log(pond.element.style);
    //pond.element.style.height= '333';
});

function openGalleryPreview(image) {
    document.getElementById("myGalleryFullscreen").style.width = "100%";  // show Gallery Fullscreen
    document.getElementById("myGalleryImage").src = image.src;  // set Gallery image to clicked
    //imageFullscreen.src = image.src;
}

function closeGalleryPreview() {
    document.getElementById("myGalleryFullscreen").style.width = "0%";  // close Gallery Fullscreen
}

function deleteGalleryPreview() {
    var image = document.getElementById("myGalleryImage");
    var host = window.location.protocol + "//" + window.location.host;
    var file_url = image.src.split(host)[1];
    var file = file_url.split('/filebank/inspection/')[1].split('/')[1]; // get only the filename ie strip out '/filebank/form/{id}/'
    var qid = file.split('-')[0];

    console.log('Delete image')
    // Create new input element with name of file to delete and add to DOM
    var input = document.createElement("input");
    input.type = "text";
    input.name = "myGalleryDelete[]";
    input.value = file;
    input.style.display = 'none';
    document.getElementById('custom_form').appendChild(input); // put it into the DOM

    var galleryDelete = document.getElementById('myGalleryDelete');

    // hide deleted file from gallery
    var thumbnail = document.getElementById('q' + qid + '-photo-' + file_url);
    console.log(thumbnail);
    thumbnail.style.display = 'none';

    //console.log(host);
    //console.log(thumbnail);

    document.getElementById("myGalleryFullscreen").style.width = "0%"; // close Gallery Fullscreen

}

function downloadGalleryPreview() {
    //alert('download');
    var image = document.getElementById("myGalleryImage");
    var host = window.location.protocol + "//" + window.location.host;
    var file_url = image.src.split(host)[1];
    var file = file_url.split('/filebank/inspection/')[1].split('/')[1]; // get only the filename ie strip out '/filebank/form/{id}/'

    // create temp <a> tag to download file
    var el = document.createElement("a");
    el.setAttribute("href", file_url);
    el.setAttribute("download", file);
    document.body.appendChild(el);
    el.click();
    el.remove();

}


