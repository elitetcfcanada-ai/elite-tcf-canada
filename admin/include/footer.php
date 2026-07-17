    </div> <!-- .main-content -->
</body>
</html>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/fr.min.js"></script>

    <script>
        window.TCF_ADMIN_API = 'api.php';
        var usersFromDB = <?php echo $users_json; ?>;
        var videosFromDB = <?php echo $videos_json; ?>;
        var topicsFromDB = <?php echo $topics_json; ?>;
        var adminsFromDB = <?php echo $admins_json; ?>;
        var messagesFromDB = <?php echo $messages_json; ?>;
        var activitiesFromDB = <?php echo $activities_json; ?>;
        var notificationsFromDB = <?php echo $notifications_json; ?>;
    </script>

    <script src="../Assets/javascript/superAdmin.js"></script>

    <?php /* Panneau profil désactivé sur les pages admin */ ?>
</body>

</html>

