// ===== Helpers =====
function showStep(id){
  document.querySelectorAll(".step").forEach(s => s.classList.remove("show"));
  document.getElementById(id).classList.add("show");
}

function toast(msg){
  const t = document.getElementById("toast");
  t.textContent = msg;
  t.classList.add("show");
  setTimeout(()=> t.classList.remove("show"), 2200);
}

// ===== Etapas =====
const stepRequest = document.getElementById("step-request");
const stepOtp = document.getElementById("step-otp");
const stepNewPass = document.getElementById("step-newpass");

const formRequest = document.getElementById("formRequest");
const formOtp = document.getElementById("formOtp");
const formNewPass = document.getElementById("formNewPass");

const contactInput = document.getElementById("contact");
const sentTo = document.getElementById("sentTo");

// OTP inputs
const otpInputs = Array.from(document.querySelectorAll(".otp__box"));

let fakeOtp = "123456"; // (front-end) exemplo. No backend você gera e envia de verdade.

// ===== 1) Solicitar código =====
formRequest.addEventListener("submit", (e) => {
  e.preventDefault();

  const contact = contactInput.value.trim();
  if(contact.length < 4){
    toast("Informe um e-mail ou telefone válido.");
    return;
  }

  sentTo.textContent = contact;

  // Aqui você liga no backend depois:
  // fetch("/api/enviar-codigo.php", { method:"POST", body: JSON.stringify({contact}) })

  toast("Código enviado (simulação: 123456).");
  showStep("step-otp");

  otpInputs.forEach(i => i.value = "");
  otpInputs[0].focus();
});

// Voltar da etapa OTP para solicitar
document.getElementById("btnBack").addEventListener("click", () => {
  showStep("step-request");
});

// Reenviar (simulação)
document.getElementById("btnResend").addEventListener("click", () => {
  // No backend: reenviar/gerar novo código
  toast("Código reenviado (simulação: 123456).");
  otpInputs.forEach(i => i.value = "");
  otpInputs[0].focus();
});

// ===== OTP comportamento (auto avançar / backspace / colar) =====
otpInputs.forEach((input, idx) => {

  input.addEventListener("input", () => {
    input.value = input.value.replace(/\D/g, "").slice(0, 1);

    if(input.value && idx < otpInputs.length - 1){
      otpInputs[idx + 1].focus();
    }
  });

  input.addEventListener("keydown", (e) => {
    if(e.key === "Backspace" && !input.value && idx > 0){
      otpInputs[idx - 1].focus();
    }
  });

});

document.addEventListener("paste", (e) => {
  const text = (e.clipboardData || window.clipboardData).getData("text");
  if(!text) return;

  const digits = text.replace(/\D/g, "").slice(0, 6);
  if(digits.length === 6){
    otpInputs.forEach((inp, i) => inp.value = digits[i]);
    otpInputs[5].focus();
  }
});

// ===== 2) Confirmar OTP =====
formOtp.addEventListener("submit", (e) => {
  e.preventDefault();

  const entered = otpInputs.map(i => i.value).join("");

  if(entered.length !== 6){
    toast("Digite os 6 dígitos.");
    return;
  }

  // Simulação:
  if(entered !== fakeOtp){
    toast("Código inválido.");
    return;
  }

  toast("Código confirmado.");
  showStep("step-newpass");
});

// ===== 3) Nova senha =====
formNewPass.addEventListener("submit", (e) => {
  e.preventDefault();

  const newPass = document.getElementById("newPass").value;
  const confirmPass = document.getElementById("confirmPass").value;

  if(newPass.length < 8){
    toast("A senha deve ter no mínimo 8 caracteres.");
    return;
  }
  if(newPass !== confirmPass){
    toast("As senhas não conferem.");
    return;
  }

  // Aqui  liga no backend depois:
  // fetch("/api/resetar-senha.php", { method:"POST", body: JSON.stringify({ newPass }) })

  toast("Senha alterada com sucesso!");
  setTimeout(() => {
    window.location.href = "index.php"; // ajuste se seu login tiver outro nome
  }, 900);
});