<!-- Modale per l'eliminazione di una voce dei bolli -->
<div class="modal fade" id="deleteTaxModal" tabindex="-1" aria-labelledby="deleteTaxModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTaxModalLabel">Elimina bollo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="deleteTaxStatus"></div>
                <p>Sei sicuro di voler eliminare questo bollo?</p>
                <form id="deleteTaxForm" method="POST" action="deleteTax.php">
                    <input type="hidden" name="id" id="deleteTaxId">
                    <button type="submit" class="btn btn-danger w-100">Elimina</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
		$('#deleteTaxModal').on('show.bs.modal', function (event) {
			var button = $(event.relatedTarget);
			var id = button.data('id');

			$('#deleteTaxId').val(id);
		});

        $('#deleteTaxForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'deleteTax.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#deleteTaxStatus').html('<div class="alert alert-success">Bollo eliminato con successo.</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#deleteTaxStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
		});
    });
</script>
