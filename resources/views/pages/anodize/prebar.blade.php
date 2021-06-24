<div class="col-12 no-padding-margin">
    <div class="col-12 no-padding-margin">
        <h3>Customer: <br /><?= $moveTag->mmt_cusnam; ?></h3>
        <table class="table" id="anoTagTable">
            <thead class="head-light">
                <th scope="col">
                    Tag No
                </th>
                <th scope="col">
                    Lot No
                </th>
                <th scope="col">
                    Die
                </th>
                <th scope="col">
                    Length
                </th>
                <th scope="col">
                    Qty
                </th>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">
                        <?= $moveTag->mmt_tagno; ?>
                    </th>
                    <td>
                        <?= $moveTag->mmt_lotno; ?>
                    </td>
                    <td>
                        <?= $moveTag->mmt_die; ?>
                    </td>
                    <td>
                        <?= $moveTag->mmt_length; ?>
                    </td>
                    <td class="totalQuantity">
                        <?= $moveTag->mmt_qty; ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <form method="POST" name="anodizeSelectBars" id="anodizeSelectBars" class="col-12">
        <div class="form-group row">
            <div class="col-12">
                <input id="itemsPerBar" type="number" class="form-control col-12" name="itemsPerBar" value="{{ old('itemsPerBar') }}" required autofocus placeholder="{{ __('items per bar?') }}" />
                <div id="totalBars"></div>
            </div>
        </div>
        @csrf
    </form>
</div>
