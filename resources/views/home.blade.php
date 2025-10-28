@extends('layouts.public')

@section('title', 'Aurora Pets')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/home-style.css') }}">
@endpush

@section('content')

  @include('partials.header')

  <section class="hero-image fade-in-b">
    <img src="{{ asset('assets/home/imgs/bg1.png') }}" alt="Aurora hero" style="width:100%;height:auto;display:block;" />
  </section>


    <div id="page-1" class="fade-in">
      <h1 class="trigger"><span class="highlights-c">Celebra la vida</span> <br>de tu mascota</h2>
  <div class="container">
  <div class="text">
          <p>Aurora es la primera <span class="highlights-b">urna compostable</span><br> diseñada para convertir las cenizas de <span class="highlights-b">nuestra mascota</span> en su árbol favorito.</p>
          <a href="https://wa.me/593999784402?text=Gracias%20por%20contactarte%20con%20auroraurn.pet%20.%20Estamos%20aquí%20para%20ayudarte%20con%20cualquier%20inquietud%20."><button>Contáctanos</button></a>
        </div>
  <span id="image-container"><img src="{{ asset('assets/home/imgs/urn1.png') }}" alt=""></span>
      </div>
      <div class="container container-2">
        <h2><span class="highlights-c">Servicio Funerario</span><br>para mascotas<p>Quito - Guayaquil - Cuenca</p></h2>
        <ul>
          <li>Servicio de cremación</li>
          <li>Urna Aurora</li>
          <li>Asistencia para la elección y plantación de la planta</li>
          <li>Perfil de usuario con información de la mascota, la planta y su cuidado</li>
        </ul>
        <h3 class="">Desde $140</h3>
      </div>
    </div>

    <div id="page-2" class="fade-in">
        <h2>¿Cómo <span class="highlights-c">Funciona?</span></h2>
        
        <div class="text">
          <p>Aurora tiene una fórmula <span class="highlights-b">100% compostable </span>que proporciona el mejor hábitat posible para la planta en crecimiento y le permite asimilar las cenizas al crecer.<br></p>
          <a href="">
          </a>
        </div>
        <div class="container">
          <span><img src="{{ asset('assets/home/imgs/urnb.png') }}" alt=""></span>
          <span class="arrow-image"><img src="{{ asset('assets/home/imgs/urn3b.png') }}" alt=""></span>
          <span class="arrow-image"><img src="{{ asset('assets/home/imgs/tree2b.png') }}" alt=""></span>
        </div>
        <p class="paragraph-2"><strong>Conoce</strong> cómo funciona la <span class="highlights-b">única urna compostable</span> en el mundo.</p>
        <a href="./nuestra-urna.html"><button>Conoce más</button></a>
      </div>

<div class="fade-in" id="page-3">
  <img src="{{ asset('assets/home/imgs/urna4.png') }}" alt="">
  <div class="text-container">
    <div class="text-1">
      <h3>Envíos dentro de <span class="highlights-c">Ecuador:<br></span></h3>
      <p>Quito - Guayaquil - Cuenca: 1 día</p>
      <p>Otras ciudades: 3 - 5 dias laborables</p>
  <img id="servientrega" src="{{ asset('assets/home/imgs/servientrega.jpeg') }}" alt="">
    </div>
    <div class="text-2">
      <h3>Resto del <span class="highlights-c">mundo</span></h3>
      <p>3 - 5 semanas</p>
  <img src="{{ asset('assets/home/imgs/dhl.jpg') }}" alt="">
    </div>
  </div>
</div>

    <div class="trigger2 fade-in" id="page-4">
      <div class="container">
        <h2>Síguenos en redes</h2>
        <div class="icons">
          <span><a href="https://www.facebook.com/profile.php?id=61558433907259"><img src="{{ asset('assets/home/imgs/fb-icon.svg') }}" alt=""></a></span>
          <span class="span-b"><a href="https://www.instagram.com/auroraurn.pet/"><img class="icon-b" src="{{ asset('assets/home/imgs/ig-icon.svg') }}" alt=""></a></span>
          <span class="span-b"><a href=""><img class="icon-b" src="{{ asset('assets/home/imgs/tiktok.svg') }}" alt=""></a></span>
        </div>
        <div class="subscribe-form">
          <h2>Sé parte de nuestro club</h2>
          <p>Te recomendamos productos, lugares pet friendly, y te mantenemos informado sobre novedades en el mundo de las plantas y las mascotas.</p>
          <form action="/send_email.php" method="post">
              <label for="email"></label>
              <input type="email" id="email" name="email" placeholder="Email:" required>
              <input type="submit" value="Subscribirse">
          </form>
        </div>
      </div>
    </div>
    
    <section id="faq-section">
      <h2>Preguntas Frecuentes</h2>
      <div class="faq">
          <div class="faq-item">
              <h3>¿Cómo funciona la urna Aurora? <span class="faq-icon">▼</span></h3>
              <p>La urna Aurora es 100% compostable y está diseñada para asimilar las cenizas de tu mascota y transformarlas en una planta 🌿. Solo necesitas colocar las cenizas de tu mascota en el interior de la urna, plantar la urna en una maceta o en el jardín y regarla  periódicamente. La urna le brinda los nutrientes y proporciona un hábitat óptimo para que las raíces se fortalezcan, creando así un nuevo ciclo de vida.</p>
          </div>
          <div class="faq-item">
              <h3>¿Cómo es el proceso de cremación? <span class="faq-icon">▼</span></h3>
              <p>En Aurora, ofrecemos cremación individual para asegurar que cada homenaje sea único y respetuoso 🐾.</p>
          </div>
          <div class="faq-item">
              <h3>¿Qué incluye el servicio funerario completo de Aurora? <span class="faq-icon">▼</span></h3>
              <p>Nuestro servicio integral incluye:
              - Recogida de los restos de tu mascota y cremación individual.
              - Urna ecológica Aurora ecológica.
              - Asesoría para elegir la planta adecuada para ti.
              - Perfil digital de la mascota y planta en nuestra aplicación web, con detalles de los cuidados y seguimiento del crecimiento.
              Te acompañamos en el proceso de adaptación de la planta.</p>
          </div>
          <div class="faq-item">
              <h3>¿Cómo se elige la planta que acompaña a la urna? <span class="faq-icon">▼</span></h3>
              <p>Ofrecemos una selección de plantas adaptables para interior o exterior, que puedes elegir en función de tu espacio y preferencias. Nuestra asesoría personalizada se asegura de que tengas la mejor opción para tu entorno, con toda la información de cuidado disponible en nuestra app.</p>
          </div>
          <div class="faq-item">
              <h3>¿Cuánto tiempo tarda el proceso de envío? <span class="faq-icon">▼</span></h3>
              <p>Realizamos envíos a nivel nacional desde nuestra planta en Quito. La entrega se realiza aproximadamente 24 horas después de que se haya confirmado el pago y se hayan enviado los datos necesarios. Nos aseguramos de que la urna llegue en perfectas condiciones🐾🌱.</p>
          </div>
          <div class="faq-item">
              <h3>¿Puedo contactar a alguien para hacer preguntas adicionales? <span class="faq-icon">▼</span></h3>
              <p>Por supuesto. Estamos aquí para ayudarte. Puedes contactarnos a través de WhatsApp o nuestras redes sociales, y uno de nuestros asesores te guiará en cada parte del proceso.
              Escríbenos al +593 99 978 4402, será un gusto atenderte.</p>
          </div>
          <div class="faq-item">
              <h3>¿Qué pasa si mi planta sufre un accidente? <span class="faq-icon">▼</span></h3>
              <p>Si la planta sufre un accidente, puedes plantar una nueva en la misma urna, ya que continuará aportando nutrientes al suelo.</p>
          </div>
      </div>
    </section>

    <div class="fade-in" id="page-5">
      <div class="container">
        <h2><span class="highlights-c">Cuidamos</span> tu planta</h2>
        <p>Ofrecemos un cuidado permanente y personalizado a través de nuestra aplicación:</p>
        <ul>
          <li>Servicio a domicilio</li>
          <li>Seguimiento con imágenes y datos sobre la planta y su crecimiento</li>
          <li>Perfil con información actualizada de la planta</li>
          <li>Comunicación directa con el jardinero encargado</li>
        </ul>
      </div>
    </div>
<footer>

<div class="container fade-in">
  <div class="info">
    <p>Quito, Ecuador.</p>
    <p>+593 9 9784 402</p>
    <p>info@aurorapets.com</p>
  </div>
  <img src="{{ asset('assets/home/imgs/logo1.png') }}" alt="">
</div>
</footer>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    $(".faq-item").click(function(){
        // Toggle 'active' class on the FAQ item
        $(this).toggleClass("active");
        
        // Toggle 'show' class on the <p> element
        $(this).children("p").toggleClass("show");
    });
                $('.fade-in').addClass('fade-in-1');
                $('.fade-in-b').addClass('fade-in-1');
});



// HEADER CHANGE & LOGO RESIZE ON SCROLL

// Function to handle intersection callback
function handleIntersection(entries, observer) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
              $('.header').addClass('header-b');
              $('.logo-header').addClass('logo-header-b');
        } else {
              $('.header').removeClass('header-b');
              $('.logo-header').removeClass('logo-header-b');
        }
    });
}

// Create an Intersection Observer
const observer = new IntersectionObserver(handleIntersection);

// Select the element you want to observe
const target = document.querySelector('.trigger');

// Start observing the target element
observer.observe(target);

// END HEADER LOGO RESIZE ON SCROLL



            // OpenSeadragon removed — replaced by a static hero image above







// Function to handle the intersection
function handleIntersectionb(entries, observer) {
    entries.forEach(entry => {
        // Check if the element is intersecting (i.e., visible)
        if (entry.isIntersecting) {
            const container = document.querySelector('.container-2');
            if (container) {
                container.classList.add('container-2b');
            } else {
                console.error("Container element not found or is null");
            }
        }
    });
}
</script>
@endpush