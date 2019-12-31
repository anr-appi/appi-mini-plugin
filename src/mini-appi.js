function populate() {
    var selectElement = document.getElementById('id_of_select');
    var option25 = document.getElementById('25b');
    var option50 = document.getElementById('50b');
    var option100 = document.getElementById('100b');
    var option250 = document.getElementById('250b');
    var option500 = document.getElementById('500b');

    var option;
    if (option25) {
        option = selectElement.children[1];
    } else if (option50) {
        option = selectElement.children[2];
    } else if (option100) {
        option = selectElement.children[3];
    } else if (option250) {
        option = selectElement.children[4];
    } else if (option500) {
        option = selectElement.children[5];
    }

    if(option !== null && option !== undefined) {
        option.setAttribute("selected", "selected");
    }
}

function ouvrirFermerSpoiler(bouton) {
    var divContenu = bouton.nextSibling;
    if (divContenu.nodeType == 3) divContenu = divContenu.nextSibling;
    if (divContenu.style.display === 'block') {
        divContenu.style.display = 'none';
    } else {
        divContenu.style.display = 'block';
    }
}
