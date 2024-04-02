document.addEventListener('DOMContentLoaded', function () {
    var tabs = document.querySelectorAll('.button-64');
    var tabContents = document.querySelectorAll('.tabContent');
    var servContent = document.getElementById('servicesContent')
    var employeeContent = document.getElementById('employeeContent');
    var bookingsContent = document.getElementById('bookingsContent');
    var accountContent = document.getElementById('accountContent');
    var newServiceContent = document.getElementById('newServiceContent');
    accountContent.style.display = 'none';

    employeeContent.style.display = 'none';
    bookingsContent.style.display = 'none';
    newServiceContent.style.display = 'none';
    servContent.style.display = 'none';
    tabs.forEach(function (tab, index) {
        tab.addEventListener('click', function () {
            tabContents.forEach(function (content) {
                content.style.display = 'none';
            });
            tabContents[index].style.display = 'flex';

           
            if(tabs[index].id === "accountTab"){
                accountContent.style.display = 'block';
                employeeContent.style.display = 'none';
                bookingsContent.style.display = 'none';
                newServiceContent.style.display = 'none';
                servContent.style.display = 'none';
            }else{
                accountContent.style.display = 'none';
            }

            if(tabs[index].id === "employeeTab"){
                accountContent.style.display = 'none';
                employeeContent.style.display = 'grid';
                bookingsContent.style.display = 'none';
                newServiceContent.style.display = 'none';
                servContent.style.display = 'none';
            }else{
                employeeContent.style.display = 'none';
            }

            if(tabs[index].id === "bookingsTab"){
                accountContent.style.display = 'none';
                employeeContent.style.display = 'none';
                bookingsContent.style.display = 'grid';
                newServiceContent.style.display = 'none';
                servContent.style.display = 'none';

            }else{
                bookingsContent.style.display = 'none';
            }

            if(tabs[index].id ==="newServiceTab"){
                accountContent.style.display = 'none';
                employeeContent.style.display = 'none';
                bookingsContent.style.display = 'none';
                newServiceContent.style.display = 'grid';
                servContent.style.display = 'none';
            }else{
                newServiceContent.style.display = 'none';
            }

            if(tabs[index].id ==="servicesTab"){
                accountContent.style.display = 'none';
                employeeContent.style.display = 'none';
                bookingsContent.style.display = 'none';
                newServiceContent.style.display = 'none';
                servContent.style.display = 'grid';
            }else{
                servContent.style.display = 'none';
            }
            
        });
    });
});