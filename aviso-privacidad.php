<?php
require_once __DIR__ . '/config/bootstrap.php';
$pageTitle  = 'Aviso de Privacidad';
$activePage = '';
require __DIR__ . '/includes/header.php';

$s = getSettings();

/* =========================================================
 *  CONFIGURACIÓN DE LA IMAGEN DE FONDO (ESTÁTICA)
 * ========================================================= */
$bgRelative     = 'images/aviso-bg.png';  // Ruta de la imagen
$bgOpacity      = 15;                      // Opacidad de la imagen (0-100) → más visible
$overlayOpacity = 10;                      // Opacidad del velo blanco (0-100) → menos velo
$bgFullPath     = BASE_PATH . '/' . $bgRelative;
$bgExists       = is_file($bgFullPath);
$bgUrl          = $bgExists ? url($bgRelative) . '?v=' . filemtime($bgFullPath) : '';
?>

<!-- =============== ESTILOS DEL FONDO =============== -->
<style>
    .privacy-bg {
        position: fixed;
        inset: 0;
        z-index: -2;
        background-size: cover;
        background-position: center center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        opacity: <?= $bgOpacity / 100 ?>;
        pointer-events: none;
    }

    .privacy-overlay {
        position: fixed;
        inset: 0;
        z-index: -1;
        background: rgba(248, 250, 252, <?= $overlayOpacity / 100 ?>);
        pointer-events: none;
    }

    /* Estilos del contenido del aviso (un solo contenedor) */
    .privacy-doc h2 {
        font-size: 1.15rem;
        font-weight: 900;
        color: #0f172a;
        border-bottom: 2px solid #f59e0b;
        padding-bottom: 0.5rem;
        margin: 2rem 0 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .privacy-doc h2:first-child { margin-top: 0; }

    .privacy-doc h3 {
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
        margin: 1.25rem 0 0.5rem;
    }

    .privacy-doc p {
        font-size: 0.875rem;
        line-height: 1.7;
        color: #334155;
        margin-bottom: 0.75rem;
        text-align: justify;
    }

    .privacy-doc strong { color: #0f172a; }

    .privacy-doc ul.checks {
        list-style: none;
        padding: 0;
        margin: 0.75rem 0 1rem;
    }
    .privacy-doc ul.checks li {
        font-size: 0.875rem;
        color: #334155;
        padding: 0.35rem 0 0.35rem 1.6rem;
        position: relative;
    }
    .privacy-doc ul.checks li::before {
        content: "\f00c";
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        position: absolute;
        left: 0;
        top: 0.35rem;
        color: #f59e0b;
        font-size: 0.75rem;
    }

    .privacy-doc ol.numbered {
        list-style: none;
        padding: 0;
        margin: 0.75rem 0 1rem;
        counter-reset: item;
    }
    .privacy-doc ol.numbered li {
        font-size: 0.875rem;
        color: #334155;
        padding: 0.4rem 0 0.4rem 2.2rem;
        position: relative;
    }
    .privacy-doc ol.numbered li::before {
        counter-increment: item;
        content: counter(item, lower-roman);
        position: absolute;
        left: 0;
        top: 0.4rem;
        background: #f59e0b;
        color: #0f172a;
        font-weight: 900;
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
    }
</style>

<!-- =============== CAPAS DE FONDO =============== -->
<?php if ($bgExists): ?>
    <div class="privacy-bg" style="background-image: url('<?= e($bgUrl) ?>');"></div>
    <div class="privacy-overlay"></div>
<?php endif; ?>

<!-- =============== HERO =============== -->
<div class="relative bg-slate-950 text-white py-20 px-4 overflow-hidden">
    <?php if ($bgExists): ?>
        <div class="absolute inset-0 bg-cover bg-center"
             style="background-image: url('<?= e($bgUrl) ?>'); opacity: 0.35;"></div>
    <?php else: ?>
        <div class="absolute inset-0 opacity-25">
            <img src="https://images.unsplash.com/photo-1450101499163-c8848c66ca85?auto=format&fit=crop&w=2000&q=80"
                 alt="Aviso de Privacidad" class="w-full h-full object-cover">
        </div>
    <?php endif; ?>
    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/70 to-transparent"></div>

    <div class="relative max-w-4xl mx-auto text-center space-y-3">
        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-400 text-xs font-semibold border border-amber-500/30">
            <i class="fa-solid fa-shield-halved"></i> Documento Legal
        </span>
        <h1 class="text-3xl sm:text-4xl font-black tracking-tight">
            Aviso de <span class="text-amber-500">Privacidad</span>
        </h1>
        <p class="text-slate-300 text-sm max-w-2xl mx-auto">
            Protección de Datos Personales en cumplimiento con la Ley Federal de Protección
            de Datos Personales en Posesión de los Particulares.
        </p>
        <p class="text-slate-500 text-xs pt-2">
            <i class="fa-solid fa-calendar"></i> Última actualización: <?= date('d \d\e F \d\e Y') ?>
        </p>
    </div>
</div>

<!-- =============== CONTENIDO (UN SOLO CONTENEDOR) =============== -->
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <article class="privacy-doc bg-white/92 backdrop-blur-md rounded-3xl shadow-2xl border border-white/60 p-8 sm:p-12">

        <!-- ============ AVISO LEGAL INICIAL ============ -->
        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-xl mb-8">
            <p class="text-xs text-amber-900 leading-relaxed m-0">
                <i class="fa-solid fa-info-circle text-amber-600 mr-1"></i>
                <strong>Mon Arka Inmobiliaria S.A.S. de C.V.</strong> pone a disposición del titular el presente
                Aviso de Privacidad, previo al tratamiento de sus datos personales, de conformidad con el
                artículo 15 de la Ley Federal de Protección de Datos Personales en Posesión de los Particulares.
            </p>
        </div>

        <!-- ============ DEFINICIONES ============ -->
        <h2><i class="fa-solid fa-book text-amber-500"></i> Definiciones</h2>
        <p>Para efectos del presente Aviso de Privacidad, se entenderá por:</p>

        <p><strong>Aviso de Privacidad:</strong> Se refiere al presente documento, el cual es puesto a disposición del TITULAR, previo al tratamiento de sus datos personales, de conformidad con el artículo 15 de la Ley Federal de Protección de Datos Personales en Posesión de los Particulares.</p>

        <p><strong>Datos personales:</strong> Cualquier información concerniente a una persona física/moral identificada o identificable.</p>

        <p><strong>Datos personales sensibles:</strong> Aquellos datos personales que afecten a la esfera más íntima del TITULAR, o cuya utilización indebida pueda dar origen a discriminación o conlleve un riesgo grave para éste. En particular, se consideran sensibles aquellos que puedan revelar aspectos como origen racial o étnico, estado de salud presente y futuro, información genética, creencias religiosas, filosóficas y morales, afiliación sindical, opiniones políticas, preferencia sexual.</p>

        <p><strong>Derechos ARCO:</strong> Se refiere a los derechos de acceso, cancelación, rectificación y oposición con los que cuenta el TITULAR en relación a sus datos personales.</p>

        <p><strong>Ley:</strong> Ley Federal de Protección de Datos Personales en Posesión de los Particulares y/o su Reglamento.</p>

        <p><strong>Responsable:</strong> Mon Arka Inmobiliaria, S.A.S. de C.V., en su carácter de persona moral privada que decide sobre el tratamiento de datos personales.</p>

        <p><strong>Titular:</strong> La persona física/moral a quien corresponden los datos personales.</p>

        <!-- ============ IDENTIDAD DEL RESPONSABLE ============ -->
        <h2><i class="fa-solid fa-building text-amber-500"></i> Identidad y Domicilio del Responsable</h2>
        <p>
            <strong>Mon Arka Inmobiliaria, S.A.S. de C.V.</strong> (en lo sucesivo "LA INMOBILIARIA"),
            es una sociedad mercantil legalmente constituida de conformidad con las leyes de la República
            Mexicana, con domicilio fiscal en <strong>Calle Baltazar Maldonado Edif. 34 Depto. 6, San Diego,
            Apizaco, Tlax., C.P. 90338</strong>, siendo RESPONSABLE del tratamiento de los datos personales
            del TITULAR que otorga su consentimiento al presente aviso.
        </p>
        <p>
            LA INMOBILIARIA, con el compromiso de observar los principios de licitud, consentimiento,
            información, calidad, finalidad, lealtad, proporcionalidad y responsabilidad en el tratamiento
            de datos personales y los derechos de privacidad y autodeterminación informativa, hace constar
            en el presente aviso de privacidad lo siguiente:
        </p>

        <!-- ============ a) DATOS TRATADOS ============ -->
        <h2><i class="fa-solid fa-user-shield text-amber-500"></i> a) Datos Personales Tratados</h2>
        <p>
            Sus datos personales que tratará LA INMOBILIARIA, incluyendo la obtención, uso, divulgación o
            almacenamiento de tales datos por cualquier medio de acceso, manejo, aprovechamiento,
            transferencia o disposición, son aquellos que usted en su calidad de TITULAR, ha proporcionado
            o proporcione a LA INMOBILIARIA y aquellos a los que LA INMOBILIARIA tiene acceso legítimamente
            por haber sido proporcionados para los fines que más adelante se señalan.
        </p>
        <p>A continuación, se enlistan algunos de los datos personales a que se refiere este apartado:</p>

        <ul class="checks">
            <li>Nombre</li>
            <li>Fecha de nacimiento</li>
            <li>CURP</li>
            <li>RFC</li>
            <li>Estado Civil</li>
            <li>Domicilio personal</li>
            <li>Género</li>
            <li>Información financiera Confidencial</li>
            <li>Información de contacto laboral, incluyendo teléfono, correo electrónico y otros.</li>
        </ul>

        <p>
            La lista anterior debe entenderse como enunciativa, más no limitativa de aquellos datos de
            carácter personal que serán tratados por LA INMOBILIARIA, en el entendido que se trata de
            datos personales de la misma naturaleza.
        </p>

        <!-- ============ b) ENCARGADOS ============ -->
        <h2><i class="fa-solid fa-sitemap text-amber-500"></i> b) Encargados del Tratamiento</h2>
        <p>
            LA INMOBILIARIA hace de su conocimiento que los datos personales del titular serán tratados
            por LA INMOBILIARIA y/o las empresas afiliadas o subsidiarias de la misma, encargados que
            actúen en nombre de LA INMOBILIARIA y terceros, distintos a LA INMOBILIARIA o al titular de
            los datos, quienes deberán cumplir con el presente Aviso de Privacidad.
        </p>

        <!-- ============ c) FINALIDADES ============ -->
        <h2><i class="fa-solid fa-bullseye text-amber-500"></i> c) Finalidades del Tratamiento</h2>
        <p>
            Las finalidades del tratamiento de los datos personales del TITULAR por parte de LA INMOBILIARIA,
            son las que se enuncian a continuación, así como todas aquellas que resulten análogas:
        </p>
        <ul class="checks">
            <li>Envío de publicidad informativa relativa a servicios inmobiliarios.</li>
            <li>Fines informativos, administrativos y/o comerciales relacionados con el objeto social de LA INMOBILIARIA.</li>
        </ul>

        <!-- ============ d) TRANSFERENCIA ============ -->
        <h2><i class="fa-solid fa-share-nodes text-amber-500"></i> d) Transferencia de Datos</h2>
        <p>
            Al accesar al presente sitio de internet y aceptar el presente Aviso de Privacidad, usted en su
            carácter de TITULAR otorga expresamente a LA INMOBILIARIA su consentimiento para la
            transferencia nacional e internacional de sus datos personales, siempre que el receptor de los
            datos asuma las mismas obligaciones asumidas por LA INMOBILIARIA. Asimismo, LA INMOBILIARIA se
            compromete a transferir solo aquella información que sea necesaria para la misma finalidad con
            la que se emite el presente aviso.
        </p>

        <!-- ============ e) MEDIDAS DE SEGURIDAD ============ -->
        <h2><i class="fa-solid fa-lock text-amber-500"></i> e) Medidas de Seguridad</h2>
        <p>
            LA INMOBILIARIA establecerá y mantendrá medidas de seguridad, administrativas, técnicas y físicas
            que permitan proteger los datos personales contra daño, pérdida, alteración, destrucción o el uso,
            acceso o tratamiento no autorizado. Estas medidas no serán menores a aquellas que mantenga
            LA INMOBILIARIA para el manejo de su propia información.
        </p>

        <!-- ============ f) DERECHOS ARCO ============ -->
        <h2><i class="fa-solid fa-scale-balanced text-amber-500"></i> f) Derechos ARCO</h2>
        <p>
            El titular de sus datos personales, podrá ejercer los derechos de acceso, cancelación,
            rectificación y oposición, respecto de sus datos (Derechos ARCO). El ejercicio de estos derechos
            se iniciará a través de la presentación de una solicitud por escrito dirigida a LA INMOBILIARIA
            mediante el correo electrónico que se menciona a continuación:
            <a href="mailto:monarcainmobiliaria@infinitummail.com" class="text-amber-600 font-bold hover:underline">monarcainmobiliaria@infinitummail.com</a>
        </p>

        <p><strong>La solicitud deberá ser presentada por el TITULAR o su representante legal, y deberá contener:</strong></p>

        <ol class="numbered">
            <li>El nombre completo del TITULAR y domicilio u otro medio para comunicarle la respuesta, incluyendo dirección de correo electrónico.</li>
            <li>Los documentos que acrediten su identidad o la del representante legal.</li>
            <li>La descripción clara y precisa de los datos personales respecto de lo que busca ejercer sus derechos.</li>
            <li>Cualquier otro elemento que facilite la localización de los datos personales del TITULAR.</li>
        </ol>

        <p>
            LA INMOBILIARIA comunicará al titular en máximo <strong>20 (veinte) días naturales</strong>,
            contados a partir de haber recibido la solicitud de acceso, rectificación, cancelación u
            oposición, la determinación adoptada, a efecto de que se haga efectiva dentro de los
            <strong>15 (quince) días naturales siguientes</strong>. Estos plazos podrán ser ampliados por
            un periodo igual cuando a discreción de LA INMOBILIARIA, las circunstancias del caso lo justifiquen.
        </p>

        <h3><i class="fa-solid fa-eye text-amber-500"></i> Derecho de Acceso</h3>
        <p>
            Procede cuando el titular desee conocer cuáles de sus datos personales obran en poder de
            LA INMOBILIARIA y el aviso de privacidad que le es aplicable. Se dará cumplimiento a una
            solicitud de acceso, poniendo a disposición del TITULAR o su representante, previo acreditamiento
            de su identidad, los documentos donde obren los datos personales requeridos, ya sea mediante
            copias fotostáticas, un CD que contenga dicha información, un dispositivo USB o cualquier otro
            medio que determine LA INMOBILIARIA. La entrega de los datos será gratuita siempre y cuando no
            se repita la solicitud de acceso en un periodo menor a 12 meses. El TITULAR únicamente cubrirá
            los costos de reproducción en copias u otros formatos.
        </p>

        <h3><i class="fa-solid fa-pen-to-square text-amber-500"></i> Derecho de Rectificación</h3>
        <p>
            El TITULAR podrá rectificar sus datos personales cuando estos sean inexactos o incompletos,
            indicando en la solicitud de rectificación las modificaciones que deban realizarse y aportando
            a LA INMOBILIARIA la documentación que sustente su petición. En caso de ser procedente la
            solicitud del TITULAR, LA INMOBILIARIA deberá informar de los cambios de que se trate a los
            encargados del tratamiento y a terceros, en caso de que haya habido transferencias de datos en
            los términos del presente Aviso de Privacidad.
        </p>

        <h3><i class="fa-solid fa-trash text-amber-500"></i> Derecho de Cancelación</h3>
        <p>
            El derecho de cancelación consiste en la supresión del dato y puede ir precedido por un periodo
            de bloqueo en el que los datos no podrán ser objeto de tratamiento. No procederá la cancelación
            de los datos personales en los casos previstos por la Ley.
        </p>

        <h3><i class="fa-solid fa-ban text-amber-500"></i> Derecho de Oposición</h3>
        <p>
            El TITULAR tendrá derecho en todo momento y por causa legítima a oponerse al tratamiento de sus
            datos. De resultar procedente la solicitud, LA INMOBILIARIA no podrá tratar los datos del TITULAR.
        </p>

        <h3><i class="fa-solid fa-circle-exclamation text-rose-500"></i> Negativa de los Derechos</h3>
        <p>
            LA INMOBILIARIA podrá negar el acceso a los datos personales, o realizar la rectificación o
            cancelación o conceder la oposición cuando el solicitante no sea el TITULAR o el representante
            legal no esté debidamente acreditado para ello, cuando en su base de datos no se encuentren los
            datos personales del titular, cuando se lesionen derechos de un tercero, cuando exista un
            impedimento legal o una resolución de una autoridad competente que restrinja el acceso a los
            datos personales o no permita su rectificación, cancelación u oposición y cuando la
            rectificación, cancelación u oposición haya sido previamente realizada.
        </p>

        <!-- ============ g) CAMBIOS ============ -->
        <h2><i class="fa-solid fa-rotate text-amber-500"></i> g) Cambios al Aviso de Privacidad</h2>
        <p>
            En caso de que se efectúen cambios al presente Aviso de Privacidad, LA INMOBILIARIA los hará
            del conocimiento del titular mediante notificación escrita que será publicada en la página de
            Internet si resulta procedente, a través del correo electrónico que el TITULAR le haya
            notificado a LA INMOBILIARIA previamente.
        </p>
        <p>
            Si el TITULAR está de acuerdo con las modificaciones hechas al Aviso de Privacidad deberá
            entregar el documento que incluya dichas modificaciones, debidamente firmado con atención a
            LA INMOBILIARIA dentro de los siguientes 5 días hábiles.
        </p>

        <!-- ============ h) REVOCACIÓN ============ -->
        <h2><i class="fa-solid fa-hand text-amber-500"></i> h) Revocación del Consentimiento</h2>
        <p>
            El consentimiento para el tratamiento de datos personales podrá ser revocado mediante aviso por
            escrito, que el TITULAR proporcione por escrito, dirigido al correo electrónico señalado en el
            presente Aviso de Privacidad, en el cual incluya las razones por las que revoca el consentimiento.
        </p>

        <!-- ============ i) TRÁMITE ============ -->
        <h2><i class="fa-solid fa-file-signature text-amber-500"></i> i) Trámite de Solicitudes</h2>
        <p>
            LA INMOBILIARIA dará trámite a las solicitudes de acceso, rectificación, cancelación y oposición,
            labor que estará a su resguardo, cuyo domicilio físico se encuentra ubicado en la dirección
            señalada al inicio del presente aviso y cuyo correo electrónico para cualquier duda o comentario
            respecto al presente Aviso de Privacidad se ha señalado anteriormente.
        </p>

        <!-- ============ ACEPTACIÓN ============ -->
        <h2><i class="fa-solid fa-file-contract text-amber-500"></i> Aceptación del Aviso</h2>
        <p>
            Al accesar al presente sitio de internet el TITULAR manifiesta conocer el presente Aviso de
            Privacidad y en consecuencia otorga su conocimiento y conformidad con su contenido, asimismo,
            manifiesta expresamente su aceptación para que LA INMOBILIARIA lleve a cabo el tratamiento de
            sus datos personales en los términos que en el mismo se señalan.
        </p>

        <!-- ============ FINALIDADES ADICIONALES ============ -->
        <h2><i class="fa-solid fa-list-check text-amber-500"></i> Finalidades Adicionales del Uso de Datos</h2>
        <p>
            Sus datos personales son utilizados y necesarios porque dan origen a la existencia y cumplimiento
            de la relación jurídica entre el TITULAR y la INMOBILIARIA, para:
        </p>
        <ul class="checks">
            <li>Gestionar y dar seguimiento administrativo a los contratos celebrados.</li>
            <li>Proveer los servicios y productos que solicita el TITULAR a la INMOBILIARIA como son: facturación, cobranza, formación e integración de expedientes y su conservación y/o actualización.</li>
            <li>Cumplir las obligaciones contraídas con el TITULAR respecto a los contratos y/o convenios celebrados.</li>
            <li>Cuando obtenemos información de otras fuentes permitidas por la Ley.</li>
            <li>Cumplir con las obligaciones de Ley.</li>
        </ul>

        <!-- ============ ACTUALIZACIONES ============ -->
        <h2><i class="fa-solid fa-bell text-amber-500"></i> Cambio o Actualización del Aviso</h2>
        <p>
            Cualquier cambio o actualización de este aviso de privacidad se publicará en los portales de
            internet de LA INMOBILIARIA, reservándose el derecho de efectuar modificaciones y actualizaciones
            al presente Aviso de Privacidad en cualquier momento para la atención de nuevas disposiciones legales.
        </p>

        <!-- ============ DATOS DE CONTACTO ============ -->
        <h2><i class="fa-solid fa-address-card text-amber-500"></i> Cómo Contactarnos</h2>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 my-6">
            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 text-center space-y-2">
                <i class="fa-solid fa-location-dot text-2xl text-amber-500"></i>
                <h4 class="font-bold text-slate-900 text-sm">Oficinas</h4>
                <p class="text-xs text-slate-600 !text-center !m-0">
                    Baltazar Maldonado Edif. 34 Depto 6.<br>
                    San Diego, C.P. 90338<br>
                    Apizaco, Tlaxcala.
                </p>
            </div>

            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 text-center space-y-2">
                <i class="fa-solid fa-phone text-2xl text-amber-500"></i>
                <h4 class="font-bold text-slate-900 text-sm">Teléfono</h4>
                <a href="tel:2414122871" class="text-xs text-amber-600 hover:text-amber-700 font-bold">
                    241-412-2871
                </a>
            </div>

            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 text-center space-y-2">
                <i class="fa-solid fa-envelope text-2xl text-amber-500"></i>
                <h4 class="font-bold text-slate-900 text-sm">Correo</h4>
                <a href="mailto:monarcainmobiliaria@infinitummail.com"
                   class="text-[11px] text-amber-600 hover:text-amber-700 font-bold break-all">
                    monarcainmobiliaria@infinitummail.com
                </a>
            </div>
        </div>

        <!-- ============ BOTÓN DE ACEPTACIÓN ============ -->
        <div class="mt-10 pt-8 border-t border-slate-200 text-center space-y-4">
            <p class="text-sm text-slate-700 !text-center">
                Si estás de acuerdo con este Aviso de Privacidad, puedes continuar navegando en nuestro sitio.
            </p>
            <div class="flex flex-wrap justify-center gap-3">
                <a href="<?= url('index.php') ?>"
                   class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-6 py-3 rounded-xl transition-all shadow-md flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-check"></i> Acepto y continúo
                </a>
                <a href="<?= url('contacto.php') ?>"
                   class="bg-slate-900 hover:bg-slate-800 text-white font-bold px-6 py-3 rounded-xl transition-all shadow-md flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-question-circle"></i> Tengo dudas
                </a>
            </div>
        </div>

    </article>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>