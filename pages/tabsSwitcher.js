document.addEventListener('DOMContentLoaded', function () {
    var tabs = document.querySelectorAll('.button-64');
    var tabContents = document.querySelectorAll('.tabContent');
    var vehicleOptions = document.getElementById('vehicleOptions');
    var editVehicleContent = document.getElementById('editVehicle');
    var addVehicleContent = document.getElementById('addVehicle');
    var boxesContent = document.getElementById('boxes3');
    var boxes2Content = document.getElementById('boxes4');

    var servicesContent = document.getElementById('servicesContent');
    var bookingsContent = document.getElementById('bookingsContent')
    var accountContent = document.getElementById('account')

    vehicleOptions.style.display = 'none';
    editVehicleContent.style.display = 'none';
    addVehicleContent.style.display = 'none';
    servicesContent.style.display = 'none';
    boxesContent.style.display = 'none';
    boxes2Content.style.display = 'none';

    tabs.forEach(function (tab, index) {
        tab.addEventListener('click', function () {
            tabContents.forEach(function (content) {
                content.style.display = 'none';
            });
            tabContents[index].style.display = 'block';

            if (tabs[index].id === 'vehicleTab') {
                vehicleOptions.style.display = 'grid';
                boxesContent.style.display = 'none';
                boxes2Content.style.display = 'none';

                editVehicleContent.style.display = 'grid';
                addVehicleContent.style.display = 'grid';
            } else {
                vehicleOptions.style.display = 'none';
                editVehicleContent.style.display = 'none';
                addVehicleContent.style.display = 'none';
                boxesContent.style.display = 'none';
                boxes2Content.style.display = 'none';
            }

            if(tabs[index].id === "servicesTab"){
                servicesContent.style.display = 'grid';
                boxesContent.style.display='none';
                boxes2Content.style.display='none';

            }else{
                boxesContent.style.display = 'none';
                boxes2Content.style.display = 'none';
                servicesContent.style.display = 'none';
            }

            if(tabs[index].id==="accountTab"){
                boxesContent.style.display='none';
                boxes2Content.style.display = 'none';

            }

            if(tabs[index].id === "bookingsTab"){
                bookingsContent.style.display = "grid";
                vehicleOptions.style.display = 'none';
                editVehicleContent.style.display = 'none';
                addVehicleContent.style.display = 'none';
                servicesContent.style.display = 'none';
                boxesContent.style.display = 'none';
                boxes2Content.style.display = 'none';
            }
        });
    });
    document.getElementById('editVehicle').addEventListener('click', function () {
        boxes2Content.style.display = 'grid';
        boxesContent.style.display = 'none';
        editVehicleContent.style.display = 'none';
        addVehicleContent.style.display = 'none';
    });

    document.getElementById('addVehicle').addEventListener('click', function () {
        boxes2Content.style.display = 'none';
        boxesContent.style.display = 'grid';
        editVehicleContent.style.display = 'none';
        addVehicleContent.style.display = 'none';
    });
});

