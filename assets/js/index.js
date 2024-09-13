const id= document.querySelector('input[name="id"]');
if(id && isEmpty(id.value)){
  const togglePassword = document.querySelector('#togglePassword');
  const password = document.querySelector('#password');
  togglePassword.addEventListener('click', function (e) {
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    if (togglePassword.src.match("https://icons.veryicon.com/png/o/miscellaneous/hekr/action-hide-password.png")) {
      togglePassword.src ="https://static.thenounproject.com/png/4334035-200.png";
    } else {
      togglePassword.src ="https://icons.veryicon.com/png/o/miscellaneous/hekr/action-hide-password.png";
    }
  }); 
}
function isEmpty(str) {
  return !str || str.trim() === '';
}

$(document).ready(function() {
  $('#email').blur(function() {
    var email = $(this).val();
    $.ajax({
      url: baseUrl + 'crudController/check_email',
      method: 'POST',
      data: { email: email },
      dataType: 'html',
      error: function() {
        $('#emailError').html("An error has occurred.");
      },
      success: function(response) {
        $('#emailError').html(response);
      }
    });
  });

  $('#phone').blur(function() {
    var phone = $(this).val();
    $.ajax({
      url: baseUrl + 'crudController/check_phone',
      method: 'POST',
      data: { phone: phone },
      dataType: 'html',
      error: function() {
        $('#phoneError').html("An error has occurred.");
      },
      success: function(response) {
        $('#phoneError').html(response);
      }
    });
  });
});
document.getElementById('submitBtn').addEventListener('click', function(event) {
  let valid = true;
  const name = document.getElementById('name').value;
  const email = document.getElementById('email').value;
  const phone = document.getElementById('phone').value;
  const gender = document.querySelector('input[name="gender"]:checked');
  const file = document.querySelector('input[name="file"]').files.length;
  const password = document.getElementById('password').value;
  const passconf = document.getElementById('passconf').value;

  document.getElementById('nameError').innerHTML = '';
  document.getElementById('emailError').innerHTML = '';
  document.getElementById('phoneError').innerHTML = '';
  document.getElementById('genderError').innerHTML = '';
  document.getElementById('fileError').innerHTML = '';
  document.getElementById('passwordError').innerHTML = '';
  document.getElementById('passconfError').innerHTML = '';

  if (!name) {
    document.getElementById('nameError').innerHTML = '<span class="error">*Please enter your full name</span>';
    valid = false;
  }

  if (!email) {
    document.getElementById('emailError').innerHTML = '<span class="error">*Please enter your email</span>';
    valid = false;
  }

  if (!phone) {
    document.getElementById('phoneError').innerHTML = '<span class="error">*Please enter your contact number</span>';
    valid = false;
  }

  if (!gender) {
    document.getElementById('genderError').innerHTML = '<span class="error">*Please select your gender</span>';
    valid = false;
  }

  if (!file) {
    document.getElementById('fileError').innerHTML = '<span class="error">*Please choose a file</span>';
    valid = false;
  }

  if (!password) {
    document.getElementById('passwordError').innerHTML = '<span class="error">*Please enter your password</span>';
    valid = false;
  }
  if (!passconf) {
    document.getElementById('passconfError').innerHTML = '<span class="error">*Please enter your confirm password</span>';
    valid = false;
  }

  if (password !== passconf) {
    document.getElementById('passconfError').innerHTML = '<span class="error">*Passwords do not match</span>';
    valid = false;
  }

  if (!valid) {
    event.preventDefault();
  }
});