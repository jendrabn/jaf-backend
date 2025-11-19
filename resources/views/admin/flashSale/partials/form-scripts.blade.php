<script>
    $(function() {
        const $startInput = $('#start_at');
        const $endInput = $('#end_at');
        const $rangeInput = $('#flash-sale-range');
        const serverFormat = 'YYYY-MM-DD HH:mm:ss';
        const displayFormat = 'DD MMM YYYY HH:mm';

        const initialStart = $startInput.val()
            ? moment($startInput.val(), serverFormat)
            : moment().add(1, 'hours').minutes(0).seconds(0);
        const initialEnd = $endInput.val()
            ? moment($endInput.val(), serverFormat)
            : initialStart.clone().add(3, 'hours');

        function updateRangeInputs(start, end) {
            const startClone = start.clone().seconds(0);
            const endClone = end.clone().seconds(0);

            $startInput.val(startClone.format(serverFormat));
            $endInput.val(endClone.format(serverFormat));
            $rangeInput.val(startClone.format(displayFormat) + ' - ' + endClone.format(displayFormat));
        }

        $rangeInput.daterangepicker({
            startDate: initialStart,
            endDate: initialEnd,
            timePicker: true,
            timePicker24Hour: true,
            timePickerSeconds: false,
            autoUpdateInput: false,
            locale: {
                format: 'YYYY-MM-DD HH:mm',
            },
        }, function(start, end) {
            updateRangeInputs(start, end);
        });

        updateRangeInputs(initialStart, initialEnd);

        let productRowIndex = {{ $nextIndex }};
        const $tableBody = $('#flash-sale-products-table tbody');
        const template = $('#flash-sale-product-row-template').html().trim();

        function formatProductOption(product) {
            if (!product.id) {
                return product.text;
            }

            const $option = $(product.element);
            const image = $option.data('image');
            const name = $option.data('name');
            const price = $option.data('price');
            const id = $option.data('id');

            return `
                <div class="d-flex align-items-center">
                    <img src="${image}" alt="${name}" style="width:40px;height:40px;object-fit:cover;border-radius:6px;margin-right:10px;">
                    <div>
                        <div><strong>[${id}] ${name}</strong></div>
                        <div class="text-muted">Rp ${price}</div>
                    </div>
                </div>
            `;
        }

        function formatProductSelection(product) {
            if (!product.id) {
                return product.text;
            }

            const $option = $(product.element);
            const name = $option.data('name');
            const id = $option.data('id');

            return `[${id}] ${name}`;
        }

        function initProductSelect($element) {
            $element.select2({
                templateResult: formatProductOption,
                templateSelection: formatProductSelection,
                escapeMarkup: function(markup) {
                    return markup;
                },
                placeholder: 'Search product...',
                width: '100%',
            });
        }

        $('.product-select').each(function() {
            initProductSelect($(this));
        });

        $('#add-product-row').on('click', function() {
            const html = template.replace(/__INDEX__/g, productRowIndex);
            const $row = $(html);

            $tableBody.append($row);
            initProductSelect($row.find('.product-select'));

            productRowIndex++;
        });

        $tableBody.on('click', '.btn-remove-row', function() {
            if ($tableBody.find('tr').length === 1) {
                toastr.warning('At least one product is required for this flash sale.');

                return;
            }

            $(this).closest('tr').remove();
        });

        const $priceModal = $('#priceCalculatorModal');
        const $priceModalOriginalPrice = $('#priceModalOriginalPrice');
        const $priceAdjustValue = $('#price-adjust-value');
        const $priceAdjustFlat = $('#price-adjust-flat');
        let currentPriceInput = null;
        let currentOriginalPrice = 0;

        const currencyFormatter = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        });

        function openPriceModal($row) {
            const $select = $row.find('.product-select');
            const selectedOption = $select.find('option:selected');

            if (!selectedOption.val()) {
                toastr.warning('Please select a product before calculating the flash price.');

                return;
            }

            currentPriceInput = $row.find('.flash-price-input');
            currentOriginalPrice = parseFloat(selectedOption.data('price-raw')) || 0;
            $priceModalOriginalPrice.text(currencyFormatter.format(currentOriginalPrice));
            $priceAdjustValue.val('');
            $priceAdjustFlat.prop('checked', true);

            $priceModal.modal('show');
        }

        $tableBody.on('click', '.btn-open-price-modal', function() {
            const $row = $(this).closest('tr');
            openPriceModal($row);
        });

        $('#saveCalculatedPrice').on('click', function() {
            if (!currentPriceInput) {
                $priceModal.modal('hide');

                return;
            }

            const discountType = $('input[name="price-adjust-type"]:checked').val();
            const discountValue = parseFloat($priceAdjustValue.val());

            if (isNaN(discountValue) || discountValue < 0) {
                toastr.warning('Please provide a valid discount value.');

                return;
            }

            let newPrice = currentOriginalPrice;
            if (discountType === 'flat') {
                newPrice -= discountValue;
            } else {
                newPrice -= (currentOriginalPrice * discountValue) / 100;
            }

            newPrice = Math.max(newPrice, 0);
            currentPriceInput.val(newPrice.toFixed(2));

            $priceModal.modal('hide');
        });

        $priceModal.on('hidden.bs.modal', function() {
            currentPriceInput = null;
            currentOriginalPrice = 0;
        });
    });
</script>
