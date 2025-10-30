function validateRegisterForm(){

  const form = document.getElementById('register_form');
  const fullname = form.elements['fullname'].value.trim();
  const dni = form.elements['dni'].value.trim().toUpperCase();
  const phone = form.elements['phone'].value.trim();
  const birthdate = form.elements['birthdate'].value.trim();
  const email = form.elements['email'].value.trim();
  if (!/^[A-Za-zÑñÁÉÍÓÚáéíóúü\s]+$/.test(fullname)){ alert('Izena txarto'); return false; }
  if (!/^[0-9]{8}-[A-Z]$/.test(dni) || !checkNif(dni)){ alert('NAN txarto'); return false; }
  if (!/^[0-9]{9}$/.test(phone)){ alert('Telefonoa txarto'); return false; }
  if (!/^\d{4}-\d{2}-\d{2}$/.test(birthdate)){ alert('Data txarto'); return false; }
  if (!/^[^@]+@[^@]+\.[^@]+$/.test(email)){ alert('Email txarto'); return false; }
  return true;
}
function validateItemForm(){
  const form = document.getElementById('item_add_form') || document.getElementById('item_modify_form');
  if (!form) return true;
  const title = form.elements['title'].value.trim();
  const year = parseInt(form.elements['year'].value,10);
  const artist = form.elements['artist'].value.trim();
  if (title.length===0){ alert('Izenburua txarto'); return false; }
  if (isNaN(year) || year<0 || year> (new Date()).getFullYear()+1){ alert('Urtea txarto'); return false; }
  if (artist.length===0){ alert('Abezlea behar da'); return false; }
  return true;
}
function checkNif(dni){
  const map = "TRWAGMYFPDXBNJZSQVHLCKE";
  const m = dni.match(/^([0-9]{8})-([A-Z])$/);
  if (!m) return false;
  const num = parseInt(m[1],10);
  const letter = m[2];
  return map.charAt(num % 23) === letter;
}
