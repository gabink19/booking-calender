document.getElementById('loginForm').addEventListener('submit', function(e){
  e.preventDefault();
  // For demo purpose, just alert input values
  const phone = document.getElementById('phone').value;
  const password = document.getElementById('password').value;
  alert(`Login dengan:\nNo. HP/ID: ${phone}\nPassword: ${'*'.repeat(password.length)}`);
  // You can replace this with your real login logic
});