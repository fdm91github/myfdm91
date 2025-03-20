<!-- Modale per l'eliminazione dello stipendio -->
<div class="modal fade" id="deleteIncomeModal" tabindex="-1" aria-labelledby="deleteIncomeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteIncomeModalLabel">Elimina entrata</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="deleteIncomeStatus"></div>
                <form id="deleteIncomeForm" method="POST">
                    <input type="hidden" id="delete_income_id" name="income_id">
                    <p>Sei sicuro di voler eliminare questa entrata?</p>
                    <button id="deleteIncomeSubmitButton" type="submit" class="btn btn-danger w-100">Elimina</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#deleteIncomeForm').submit(function(e) {
            e.preventDefault();
            var $form = $(this);
            $form.find('button[type="submit"]').prop('disabled', true); // Disabilita il pulsante di invio
            $.ajax({
                url: 'deleteIncome.php',
                type: 'POST',
                data: $form.serialize(),
                success: function(response) {
                    $('#deleteIncomeStatus').html('<div class="alert alert-success">Entrata eliminata con successo!</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#deleteIncomeStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                },
                complete: function() {
                    $form.find('button[type="submit"]').prop('disabled', false); // Riabilita il pulsante di invio
                }
            });
        });
		$('#deleteIncomeModal').on('show.bs.modal', function (event) {
			var button = $(event.relatedTarget); // Button that triggered the modal
			var incomeId = button.data('id'); // Extract info from data-* attributes
			var modal = $(this);
			modal.find('#delete_income_id').val(incomeId);
		});
    });
</script>