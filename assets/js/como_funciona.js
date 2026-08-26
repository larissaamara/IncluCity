
const steps = document.querySelectorAll('.step');

function mostrarSteps() {
  steps.forEach(step => {
    const top = step.getBoundingClientRect().top;

    if (top < window.innerHeight - 50) {
      step.classList.add('show');
    }
  });
}

window.addEventListener('scroll', mostrarSteps);

// ativa ao carregar
mostrarSteps();
