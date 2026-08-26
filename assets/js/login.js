
function verSenha(id){
  const campo = document.getElementById(id);

  if(campo.type === "password"){
    campo.type = "text";
  } else {
    campo.type = "password";
  }
} 
