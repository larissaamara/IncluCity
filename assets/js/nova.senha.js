function verSenha(id) {
    const campo = document.getElementById(id);

    if (campo.type === "password") {
        campo.type = "text";
    } else {
        campo.type = "password";
    }
}


function validarNovaSenha() {

    const novaSenha = document.getElementById("novaSenha").value;
    const confirmarNovaSenha = document.getElementById("confirmarNovaSenha").value;

    const senhaForte = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.#_-]).{8,}$/;

    if (!senhaForte.test(novaSenha)) {
        alert("A senha deve ter no mínimo 8 caracteres, com maiúscula, minúscula, número e símbolo.");
        return false;
    }

    // Verifica se as duas senhas são iguais
    if (novaSenha !== confirmarNovaSenha) {
        alert("As senhas não são iguais.");
        return false;
    }

    return true;
}
