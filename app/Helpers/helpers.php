<?php

if (!function_exists('formatRupiah')) {
    function formatRupiah($price)
    {
        return 'Rp ' . number_format($price, 0, ',', '.');
    }
}

