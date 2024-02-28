// Get a reference to the file input element
const inputElement = document.querySelector('input[type="file"]');

// Create a FilePond instance
const pond = FilePond.create(inputElement, {
    onaddfilestart: (file) => {
        isLoadingCheck();
    },
    onprocessfile: (files) => {
        isLoadingCheck();
    }
});
FilePond.setOptions({
    server: {
        url: '/file/upload',
        fetch: null,
        revert: null,
        headers: {'X-CSRF-TOKEN': $('meta[name=token]').attr('value')},
    },
    allowMultiple: true,
});

function isLoadingCheck() {
    const elem = document.getElementById("submit");
    var isLoading = pond.getFiles().filter(x => x.status !== 5).length !== 0;
    if (isLoading) {
        elem.setAttribute("disabled", "");
    } else {
        elem.removeAttribute("disabled");
    }
}