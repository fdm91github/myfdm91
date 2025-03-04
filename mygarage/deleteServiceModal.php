<!-- Modale per l'eliminazione di una voce delle manutenzioni -->
<div class="modal fade" id="deleteServiceModal" tabindex="-1" aria-labelledby="deleteServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteServiceModalLabel">Elimina manutenzione</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="deleteServiceStatus"></div>
                <p>Sei sicuro di voler eliminare questa manutenzione?</p>
                <form id="deleteServiceForm" method="POST" action="deleteService.php">
                    <input type="hidden" name="id" id="deleteServiceId">
                    <button type="submit" class="btn btn-danger w-100">Elimina</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#deleteServiceModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            $('#deleteServiceId').val(id);
        });

        $('#deleteServiceForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'deleteService.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#deleteServiceStatus').html('<div class="alert alert-success">Manutenzione eliminata con successo.</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#deleteServiceStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
        });
    });
</script>
