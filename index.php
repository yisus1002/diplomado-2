<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/global.scss">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <title>Banco</title>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="./assets/js/main.js"></script>
</head>
<body>
    <?php include_once "./pages/shared/navbar/nabar.html"; ?>

    <main id="main " class="cantainer-fluid h-auto">
      <div class="my-2 " id="salir">
      </div>
        <div class="cantainer" > 
                <div class="col d-flex flex-wrap w-100 my-4" id="ingresar">
                       
                </div> 
        </div>
    </main>
    <script>
      let response;
      let numero_cuenta;
      let saldo_cuenta ;
      let usuario_id ;
      let ingresar = `
      <div class="card border-0 shadow p-4" style="width: 18rem; margin: auto; ">
                        <div id="login-form m-a">
                    <h2 class="mb-3">Iniciar Sesión</h2>
                    <form id="ingresar_cuenta" action="./services/api.php" method="post">

                    <input type="hidden" name="action" value="login">
                        <div class="form-group">
                            <label for="nombre_usuario">Nombre de Usuario:</label>
                            <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" required>
                        </div>
                        <div class="form-group">
                            <label for="contrasena">Contraseña:</label>
                            <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                        </div>
                        <button type="submit" class="btn btn-primary my-4">Iniciar Sesión</button>
                    </form>
                </div>
                        </div>
      `;
      let cuenta = `
      <div class="col-6 mb-2">
  <div class="roll-in-left card border-0 shadow p-2 col-10 h-100 " style=" margin: auto; ">
    <div class="card-body d-flex flex-column justify-content-center">
          <div class="mb-3 ">
            <h1  class="h2">Cuenta : <span id="cuenta"></span></h1>
            <h2  class="h3">Saldo : $ <span id="saldo"></span></h2>
          </div>
    </div>
  </div>
</div>
<div class="col-6 mb-2 g-2 d-flex">
  <div class="slide-in-top card border-0 shadow p-2 col-5" style=" margin: auto; ">
    <div class="card-body">
      <form id="formConsignar"  >
        <h1 class="h3">Consignar</h1>
          <div class="mb-3">
          <input type="hidden" name="numero_cuenta" id="numero_cuenta" value="">
            <label for="numero_cuenta" class="form-label">Ingrese el valor a consignar</label>
            <input type="number" min="0" class="form-control" name="monto" required>
          </div>
          <button type="button" onclick="consignar()" class="btn btn-success w-10" style="display: flex; margin: auto;">Consignar</button>
        </form>
    </div>
  </div>
  <div class="card border-0 shadow p-2 col-5" style=" margin: auto; ">
    <div class="card-body">
      <form id="formRetirar" >
        <h1 class="h3">Retirar</h1>
          <div class="mb-3">
          <input type="hidden" name="numero_cuenta" id="numero_cuenta" value="">
            <label for="retirar_cuenta" class="form-label">Ingrese el valor a retirar</label>
            <input type="number" min="0" class="form-control" id="retirar_cuenta" name="monto" required>
          </div>
          <button type="button" onclick="retirar()" class="btn btn-danger w-10" style="display: flex; margin: auto;">Retirar</button>
        </form>
    </div>
  </div>
</div>
<div class="col-12 mb-y-4">
  <div class=" card border-0 shadow p-2 col-4 h-100 " style=" margin: auto; ">
    <div class="card-body">
      <form id="formTtransferir" >
        <h1 class="h3">Transferir</h1>
          <div class="mb-3">
            <label for="obtenercuenta" class="form-label" name="numerocuenta">Ingrese el numero de cuenta</label>
            <input type="number" class="form-control" id="obtenercuenta" name="cuenta" required>
            <label for="obtenercuenta" class="form-label">Ingrese el valor a transferir</label>
            <input type="number" min="0" class="form-control" id="obtenercuenta" name="monto" required>
          </div>
          <button type="button" onclick="transferir()" class="btn btn-info w-10" style="display: flex; margin: auto;">Transferir</button>
        </form>
    </div>
  </div>
</div>
      `;
      $(document).ready(function () {
        document.getElementById('ingresar').innerHTML = ingresar;
      });

      function enviarFormulario(num_cuenta, valor, action, num_cuenta_transf, usuario_id) {
  return new Promise(function(resolve, reject) {
    var settings = {
      "url": "http://localhost/banco/services/api.php",
      "method": "POST",
      "timeout": 0,
      "headers": {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      "data": {
        "numero_cuenta": num_cuenta,
        "action": action,
        "monto": valor,
        "cuenta_destino": num_cuenta_transf,
        "cuenta_origen": num_cuenta,
        "usuario_id": usuario_id,
      }
    };

    $.ajax(settings).done(function (response) {
      resolve(response);
    }).fail(function (error) {
      reject(error);
    });
  });
}
  
      function manejarFormulario(formulario, callback) {
            $(document).ready(function () {
                $(formulario).off('submit').submit(function (event) {
                    event.preventDefault();
                    var form = $(this);
                    $.ajax({
                        type: form.attr("method"),
                        url: form.attr("action"),
                        data: form.serialize(),
                        success: function (data) {
                            if (callback && typeof callback === 'function') {
                                callback(data);
                            }
                        },
                        error: function (data) {
                            console.log("Error en la solicitud AJAX");
                        }
                    });
                    return false;
                });
            });
        }
        manejarFormulario("#ingresar_cuenta", function (data) {
            console.log(data);
                    guardar(data);
        });
        // (num_cuenta, valor, action, num_cuenta_transf, usuario_id)
        function consignar() {
          var formulario = document.getElementById("formConsignar");
          var monto = formulario.querySelector('[name="monto"]').value;
          if(!monto || monto==="0"){
            alert("Por favor, ingrese un valor valido para consignar", "error");
            return
          }
         let data= enviarFormulario(numero_cuenta, monto, "consignar", "", usuario_id)
         .then(function(respuesta) {
          console.log("Resultado:", respuesta);
          if(respuesta.status==="success"){
            saldo_cuenta=respuesta.nuevo_saldo;
            formulario.querySelector('[name="monto"]').value=0;
            document.getElementById("saldo").innerText = respuesta.nuevo_saldo;
          }else{
            alert(respuesta.message, "error");
          }
          })
          .catch(function(error) {
            console.error("Error:", error);
          });
        //  console.log(data);
        
        }

        function retirar(){
          var formulario = document.getElementById("formRetirar");
          var monto = formulario.querySelector('[name="monto"]').value;
          if(!monto || monto==="0"){
            alert("Por favor, ingrese un valor valido para retirar", "error");
          }
          let data= enviarFormulario(numero_cuenta, monto, "retirar", "",usuario_id)
         .then(function(respuesta) {
          console.log("Resultado:", respuesta);
          if(respuesta.status==="success"){
          saldo_cuenta=respuesta.nuevo_saldo;
          formulario.querySelector('[name="monto"]').value=0;
          document.getElementById("saldo").innerText = respuesta.nuevo_saldo;
          }else{
            alert(respuesta.message, "error");
          }
          })
          .catch(function(error) {
            console.error("Error:", error);
          });
        }
        function transferir(){
          var formulario = document.getElementById("formTtransferir");
          var monto = formulario.querySelector('[name="monto"]').value;
          var cuenta = formulario.querySelector('[name="cuenta"]').value;
          console.log({
            monto,
            cuenta
          });
          let data= enviarFormulario(numero_cuenta, monto, "transferir", cuenta, usuario_id)
          .then(function(respuesta) {
          console.log("Resultado:", respuesta);
          if(respuesta.status==="success"){
          saldo_cuenta=saldo_cuenta-monto;
          formulario.querySelector('[name="monto"]').value=0;
          document.getElementById("saldo").innerText = saldo_cuenta;
          alert("Transferencia exitosa", "success");
          }else{
            alert(respuesta.message, "error");
          }
          })
          .catch(function(error) {
            console.error("Error:", error);
          });
        }


    function salir(){
        let data= enviarFormulario("", "", "cerrar_sesion", "", "")
        .then(function(respuesta){
            console.log(respuesta)
        })
        .catch(function(err){
            console.log(err);
        })
      document.getElementById("ingresar").innerHTML = ingresar;
      document.getElementById("salir").innerHTML = "";
      window.location.reload();
    }
function guardar(data) {
    response = data;
    if(response.status==="success"){
      document.getElementById("salir").innerHTML =`<button type="button" class="btn btn-primary float-end mx-2 my-2" onclick="salir()">Salir</button>`;
      document.getElementById("ingresar").innerHTML = "";
      document.getElementById("ingresar").innerHTML = cuenta;
      numero_cuenta = response?.infoUsuario?.numero_cuenta;
      saldo_cuenta = response?.infoUsuario?.saldo;
      usuario_id = response?.infoUsuario?.id;
      document.getElementById("cuenta").innerText = numero_cuenta
      document.getElementById("saldo").innerText = saldo_cuenta;
      $("#numero_cuenta").val(numero_cuenta);
    }else{
      alert(response?.message, "error");
    }
  }


  function alert(mensaje, icon){
    const Toast = Swal.mixin({
  toast: true,
  position: "top-end",
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.onmouseenter = Swal.stopTimer;
    toast.onmouseleave = Swal.resumeTimer;
  }
});
Toast.fire({
  icon: icon,
  title: mensaje
});
  }
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>