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
            //console.log('File added');
            //console.log(fileItem);
            //console.log(fileItem.fileType);
            //console.log(fileItem.getMetadata('resize'));
            //console.log(inputElement);
            //console.log(inputElement.name);
            //console.log(inputElement.element.style);
        },

        // alter the output property
        onpreparefile: (fileItem, output) => {
            //console.log('File prepared');
            //console.log('fileType:' + fileItem.fileType);
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
                img.dataset.filepondId = fileItem.id;

                //console.log('Ele:' + inputElement);

                var gallery = document.getElementById(question + '-gallery');
                gallery.appendChild(img);

                var thumbnail = document.getElementById(img.id);
                thumbnail.style.opacity = '0.5';
                //console.log(thumbnail);
            }
        },

        onprocessfile: (err, fileItem) => {
            if (err) return;

            const imageTypes = ['image/png', 'image/jpeg', 'image/gif'];
            if (!imageTypes.includes(fileItem.fileType)) return;

            const question = inputElement.name.split('-media[')[0];
            const thumbnail = document.getElementById(
                `${question}-photo-${fileItem.id}`
            );

            if (!thumbnail) return;

            // FilePond returns a STRING serverId
            const tmpFolder = fileItem.serverId;

            if (!tmpFolder) return;

            thumbnail.dataset.tmpFolder = tmpFolder;
            thumbnail.dataset.filename = fileItem.filename;

            thumbnail.style.display = 'inline';
            thumbnail.style.opacity = '0.5';
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
    //document.getElementById("myGalleryFullscreen").style.width = "100%";  // show Gallery Fullscreen
    //document.getElementById("myGalleryImage").src = image.src;  // set Gallery image to clicked
    //imageFullscreen.src = image.src;

    const fullscreen = document.getElementById("myGalleryFullscreen");
    const viewer = document.getElementById("myGalleryImage");

    fullscreen.style.width = "100%";

    viewer.src = image.src;

    // propagate FileBank metadata
    viewer.dataset.filebankPath = image.dataset.filebankPath;
    viewer.dataset.filename = image.dataset.filename;
}

function closeGalleryPreview() {
    document.getElementById("myGalleryFullscreen").style.width = "0%";  // close Gallery Fullscreen
}

function deleteGalleryPreview() {
    const image = document.getElementById("myGalleryImage");
    const path = image.dataset.filebankPath;

    if (!path) {
        console.error('Missing FileBank path');
        return;
    }

    fetch('/form/media', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="token"]').content,
        },
        body: JSON.stringify({
            path: path,
            form_id: document.querySelector('[name="form_id"]').value
        }),
    })
        .then(res => {
            if (!res.ok) throw new Error('Delete failed');
            return res.json();
        })
        .then(() => {
            // Hide thumbnail
            const thumb = document.querySelector(`[data-filebank-path="${path}"]`);
            if (thumb) thumb.remove();

            // Close fullscreen
            document.getElementById("myGalleryFullscreen").style.width = "0%";
        })
        .catch(err => {
            console.error(err);
            alert('Unable to delete file');
        });
}

function deleteGalleryPreview2() {
    const image = document.getElementById("myGalleryImage");

    console.log('DELETE HANDLER HIT – NEW VERSION');
    console.log(image.dataset);

    const path = image.dataset.filebankPath;
    const filename = image.dataset.filename;

    if (!path) {
        console.error('Missing FileBank path on image', image);
        return;
    }

    // create hidden input for backend
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = "myGalleryDelete[]";
    input.value = path;
    document.getElementById('custom_form').appendChild(input);

    // hide thumbnail
    const thumb = document.querySelector(
        `[data-filebank-path="${path}"]`
    );
    if (thumb) thumb.style.display = 'none';

    document.getElementById("myGalleryFullscreen").style.width = "0%";
}


function downloadGalleryPreview() {
    const image = document.getElementById("myGalleryImage");

    const path = image.dataset.filebankPath;
    const filename = image.dataset.filename;

    if (!path || !filename) {
        console.error('Missing FileBank metadata');
        return;
    }

    // Always download via FileBank proxy
    const url = `/filebank/${path}`;

    const a = document.createElement("a");
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    a.remove();
}

