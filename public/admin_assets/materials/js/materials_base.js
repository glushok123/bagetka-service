$(document).ready(function () {
    updateList()

    function updateList() {
        $('#block-spinner').show();

        listNov.html(' ')
        listAr.html(' ')
        listBar.html(' ')

        getCollectionMaterials('Новокузнецкая');
        getCollectionMaterials('Арбатская');
        getCollectionMaterials('Баррикадная');
        getCollectionMaterials();

        $('#block-spinner').hide();
    }
})