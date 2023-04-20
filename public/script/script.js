selectVille();

function getSelect(selector) {
    return document.querySelector('#sortie_'+selector);
}

function getSelectorData(selector) {
    const form = selector.closest('form');
    let data = selector.name + '=' + selector.value;
    return  fetch(
        form.action, {
            method: form.getAttribute('method'),
            body: data,
            headers: {
                "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8;"
            }
        })
        .then(data => data.text())
}

function selectVille() {
    let ville = getSelect('ville');
    let codePostal = getSelect('codePostal');
    ville.addEventListener('change', () => {
        getSelectorData(ville)
            .then(html => {
                let content = document.createElement("html");
                content.innerHTML = html;
                let newLieu = content.querySelector('#sortie_lieu');
                newLieu.classList.remove('disable');
                getSelect('lieu').replaceWith(newLieu);
                codePostal.value = getOptions(ville, 'postal');
                selectLieu()
            })
    })
}

function selectLieu() {
    let lieu = getSelect('lieu');
    lieu.addEventListener('change', () => {
        const options = ['rue', 'latitude', 'longitude'];
        for (let i in options) {
            const item = options[i];
            getSelect(item).value = getOptions(lieu, item);
        }
    })
}

function getOptions(selector, option) {
    return selector.options[selector.selectedIndex].getAttribute('data-'+ option);
}

const lieuForm = document.querySelector('.sortie-lieu-modal')

function showLieuForm() {
    lieuForm.style.display = 'block';
    const sortieForm = document.querySelector('.sortie-form');
    const form = sortieForm.closest('form');
    const datas = new FormData(form);
    const formData = {}
    for (let data of datas) {
        formData[data[0]] = data[1]
    }
    sessionStorage.setItem('formData', JSON.stringify(formData));
    const getSession = sessionStorage.getItem('formData');
    console.log(JSON.parse(getSession));
}

window.onclick = function (event) {
    if (event.target === lieuForm) {
        lieuForm.style.display = 'none';
    }
}

window.addEventListener('load',() => {
    if (this.sessionStorage.getItem('formData')) {
        const formData = JSON.parse(this.sessionStorage.getItem('formData'));
        for (let [key, dataValue] of Object.entries(formData)) {
            const query = key.slice(7, -1);
            const select = document.querySelector('#sortie_' + query)
            if (select != null) {
                select.value = dataValue;
            }
        }
        sessionStorage.removeItem('formData');
    }
})