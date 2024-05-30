<!doctype html>
<html lang="zxx">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <!-- Links of CSS files -->
        <link rel="stylesheet" href="{{ base_url() }}themes/frontend/assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="{{ base_url() }}themes/frontend/assets/css/animate.min.css">
        <link rel="stylesheet" href="{{ base_url() }}themes/frontend/assets/css/boxicons.min.css">
        <link rel="stylesheet" href="{{ base_url() }}themes/frontend/assets/css/flaticon.css">
        <link rel="stylesheet" href="{{ base_url() }}themes/frontend/assets/css/nice-select.min.css">
        <link rel="stylesheet" href="{{ base_url() }}themes/frontend/assets/css/rangeSlider.min.css">
        <link rel="stylesheet" href="{{ base_url() }}themes/frontend/assets/css/fancybox.min.css">
        <link rel="stylesheet" href="{{ base_url() }}themes/frontend/assets/css/meanmenu.min.css">
        <link rel="stylesheet" href="{{ base_url() }}themes/frontend/assets/css/owl.carousel.min.css">
        <link rel="stylesheet" href="{{ base_url() }}themes/frontend/assets/css/magnific-popup.min.css">
        <link rel="stylesheet" href="{{ base_url() }}themes/frontend/assets/css/style.css">
        <link rel="stylesheet" href="{{ base_url() }}themes/frontend/assets/css/responsive.css">
        <link rel="stylesheet" href="{{ base_url() }}themes/frontend/assets/css/dark-style.css">


        <title>Surveiku</title>

        <link rel="icon" type="image/png" href="{{ base_url() }}themes/frontend/assets/img/favicon.png">

		<script src="https://www.google.com/recaptcha/api.js" async defer></script>
    </head>
    <body>

        <!-- Start Preloader Area -->
        <div class="preloader">
            <div class="loader">
                <div class="sbl-half-circle-spin">
                    <div></div>
                </div>
            </div>
        </div>
        <!-- End Preloader Area -->

@php
$ci = get_instance();
@endphp


        <!-- Start Membership Levels Area -->
        <section class="membership-levels-area ptb-100">
            <div class="container">
                <div class="membership-levels-table table-responsive">
                    <table class="table table-striped">
                        <thead>
                            
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="2"><?php if ($user->foto_profile == NULL) : ?>
                <img src="{{ base_url() }}assets/klien/foto_profile/200px.jpg" alt="Card image">
                <?php else : ?>
                <img src="<?php echo base_url(); ?>assets/klien/foto_profile/<?php echo $user->foto_profile ?>"
                    alt="Card image">
                <?php endif; ?></td>
                            </tr>
                            <tr>
                                <td>Pemegang Sertifikat</td>
                                <td><?php echo $user->company ?></td>
                            </tr>
                            <tr>
                                <td>Nomor Sertifikat</td>
                                <td><?php echo $manage_survey->nomor_sertifikat ?></td>
                            </tr>
                            <tr>
                                <td>Nama Survei</td>
                                <td><?php echo $manage_survey->survey_name ?></td>
                            </tr>
                            <tr>
                                <td>Tanggal Survei</td>
                                <td><?php echo $manage_survey->survey_mulai ?> s/d
                    <?php echo $manage_survey->survey_selesai ?></td>
                            </tr>
                            <tr>
                                <td>Jenis Pelayanan</td>
                                <td><?php echo $manage_survey->nama_klasifikasi_survei ?> -
                    <?php echo $manage_survey->nama_jenis_pelayanan_responden ?></td>
                            </tr>
                            <tr>
                                <td>Metode Sampling</td>
                                <td><?php echo $manage_survey->nama_sampling ?></td>
                            </tr>
                            <tr>
                                <td>Sample Minimal</td>
                                <td><?php echo $manage_survey->jumlah_sampling ?> Orang</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        <!-- End Membership Levels Area -->

		<!-- Start Detail Area -->
        <section class="services-details-area pb-100 pt-70">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-md-12">
                        <div class="download-file">
                            <h3>Nilai SKM</h3>
                            <ul>
                                <li><a href="#"><?php echo ROUND($ikm, 2) ?></a></li>
                                <li><a href="#">Kinerja Unit Pelayanan: </a><br>
                                <b> <?php
                            foreach ($definisi_skala->result() as $obj) {
                                if ($ikm <= $obj->range_bawah && $ikm >= $obj->range_atas) {
                                    echo  $obj->kategori;
                                }
                            }
                            if ($ikm <= 0 || $ikm == NULL) {
                                echo  'NULL';
                            }

                            // if ($ikm <= 100 && $ikm >= 88.31) {
                            //     echo 'SANGAT BAIK';
                            // } elseif ($ikm <= 88.40 && $ikm >= 76.61) {
                            //     echo 'BAIK';
                            // } elseif ($ikm <= 76.60 && $ikm >= 65) {
                            //     echo 'KURANG BAIK';
                            // } elseif ($ikm <= 64.99 && $ikm >= 25) {
                            //     echo 'TIDAK BAIK';
                            // } else {
                            //     echo 'NULL';
                            // }
                            ?></b></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-12">
                        <div class="services-contact-info">
                            <h3>Responden</h3>
                            <ul>
                                <li><div class="icon">
                                        <i class='bx bx-user-pin'></i>
                                    </div>
                                    <span>Jumlah Responden:</span><?php echo $jumlah_kuisioner ?> Orang</li>
                                <?php
                                foreach ($profil->result() as  $row) {
                                ?>
                                <li>
                                    <!--<div class="icon">
                                        <i class='bx bx-user-pin'></i>
                                    </div>-->
                                    <span><b><?php echo $row->nama_profil ?></b>:</span><br>
                                    <?php
                                $kategori_profil_responden = $ci->db->query("SELECT *, (SELECT COUNT(*) FROM responden_$manage_survey->table_identity JOIN survey_$manage_survey->table_identity ON responden_$manage_survey->table_identity.id = survey_$manage_survey->table_identity.id_responden WHERE kategori_profil_responden_$manage_survey->table_identity.id = responden_$manage_survey->table_identity.$row->nama_alias && is_submit = 1) AS perolehan FROM kategori_profil_responden_$manage_survey->table_identity");

                                foreach ($kategori_profil_responden->result() as $value) {
                                ?>
                            <?php if ($value->id_profil_responden == $row->id) { ?>

                            - <?php echo $value->nama_kategori_profil_responden ?> :
                                <?php echo $value->perolehan ?> Orang<br>

                            <?php } ?>

                            <?php } ?>
                                </li>
                                <?php
                                }
                                ?>
                                <li><div class="icon">
                                        <i class='bx bx-user-pin'></i>
                                    </div>
                                    <span>Periode Survey</span> <?php echo $manage_survey->survey_mulai ?> s/d
                        <?php echo $manage_survey->survey_selesai ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- End Detail Area -->
        

        <div class="go-top"><i class="flaticon-up"></i></div>

        <!-- Links of JS files -->
        <script src="{{ base_url() }}themes/frontend/assets/js/jquery.min.js"></script>
        <script src="{{ base_url() }}themes/frontend/assets/js/bootstrap.bundle.min.js"></script>
        <script src="{{ base_url() }}themes/frontend/assets/js/owl.carousel.min.js"></script>
        <script src="{{ base_url() }}themes/frontend/assets/js/magnific-popup.min.js"></script>
        <script src="{{ base_url() }}themes/frontend/assets/js/tilt.jquery.min.js"></script>
        <script src="{{ base_url() }}themes/frontend/assets/js/meanmenu.min.js"></script>
        <script src="{{ base_url() }}themes/frontend/assets/js/nice-select.min.js"></script>
        <script src="{{ base_url() }}themes/frontend/assets/js/rangeSlider.min.js"></script>
        <script src="{{ base_url() }}themes/frontend/assets/js/sticky-sidebar.min.js"></script>
        <script src="{{ base_url() }}themes/frontend/assets/js/fancybox.min.js"></script>
        <script src="{{ base_url() }}themes/frontend/assets/js/isotope.pkgd.min.js"></script>
        <script src="{{ base_url() }}themes/frontend/assets/js/TweenMax.min.js"></script>
        <script src="{{ base_url() }}themes/frontend/assets/js/ScrollMagic.min.js"></script>
        <script src="{{ base_url() }}themes/frontend/assets/js/animation.gsap.min.js"></script>
        <script src="{{ base_url() }}themes/frontend/assets/js/debug.addIndicators.min.js"></script>
        <script src="{{ base_url() }}themes/frontend/assets/js/wow.min.js"></script>
        <script src="{{ base_url() }}themes/frontend/assets/js/form-validator.min.js"></script>
        <script src="{{ base_url() }}themes/frontend/assets/js/contact-form-script.js"></script>
        <script src="{{ base_url() }}themes/frontend/assets/js/ajaxchimp.min.js"></script>
        <script src="{{ base_url() }}themes/frontend/assets/js/main.js"></script>

		
    </body>
</html>