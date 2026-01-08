<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanner de Portas - Verifique Portas Abertas em IPs e Domínios</title>
    <meta name="description" content=" Ferramenta online gratuita para verificar a disponibilidade de IPs e domínios. Teste a conectividade de servidores e descubra se estão acessíveis na internet.">
    <meta name="keywords" content="scanner de portas, verificar portas, portas abertas, IP, domínio, conectividade, firewall, rede, tcp, scan">
    <meta name="author" content="Scanner de Portas">
    <meta name="robots" content="index, follow">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #111424;
            color: #ffffff;
        }
        .card {
            background: rgba(30, 33, 58, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(14, 229, 127, 0.2);
        }
        .btn-primary {
            background: linear-gradient(90deg, #0EE57F, #0BA861);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(14, 229, 127, 0.4);
        }
        .status-open {
            color: #0EE57F;
        }
        .status-closed {
            color: #ff4d4d;
        }
        table th {
            color: #0EE57F;
        }
        input, select {
            background: rgba(20, 23, 40, 0.7);
            border: 1px solid rgba(14, 229, 127, 0.3);
            color: #fff;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #0EE57F;
            box-shadow: 0 0 0 3px rgba(14, 229, 127, 0.2);
        }
    </style>
</head>
<body class="min-h-screen p-4 flex flex-col items-center">
    <div class="max-w-4xl w-full flex flex-col items-center">
        <!-- Cabeçalho com Logo -->
        <header class="flex flex-col items-center mb-10 mt-6">
            <img src="logo.png" alt="Logo Scanner de Portas" class="w-40 h-40 object-contain mb-4">
            <h1 class="text-4xl font-bold text-center bg-clip-text text-transparent bg-gradient-to-r from-[#0EE57F] to-[#0BA861]">
                Scanner de Portas
            </h1>
            <p class="text-gray-400 mt-2 text-center">Verifique a disponibilidade de portas em qualquer IP ou domínio</p>
        </header>

        <!-- Formulário -->
        <div class="card p-8 rounded-2xl shadow-2xl w-full max-w-2xl mb-10 transition-all duration-300 hover:shadow-[0_0_25px_rgba(14,229,127,0.2)]">
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="target" class="block text-sm font-medium text-gray-300 mb-2">IP ou Domínio:</label>
                        <input
                            type="text"
                            id="target"
                            name="target"
                            value="<?php echo htmlspecialchars($_SERVER['REMOTE_ADDR']); ?>"
                            required
                            class="w-full px-4 py-3 rounded-lg focus:ring-2 focus:ring-[#0EE57F] transition-all"
                            placeholder="Ex: 192.168.0.1 ou exemplo.com"
                        >
                    </div>
                    <div>
                        <label for="ports" class="block text-sm font-medium text-gray-300 mb-2">Portas (separadas por vírgula):</label>
                        <input
                            type="text"
                            id="ports"
                            name="ports"
                            value="22,80,443"
                            required
                            class="w-full px-4 py-3 rounded-lg focus:ring-2 focus:ring-[#0EE57F] transition-all"
                            placeholder="Ex: 22,80,443"
                        >
                    </div>
                </div>
                <div class="flex justify-center">
                    <button
                        type="submit"
                        class="btn-primary text-white font-bold py-3 px-10 rounded-full text-lg shadow-lg"
                    >
                        Verificar Portas
                    </button>
                </div>
            </form>
        </div>

        <!-- Resultado -->
        <?php
        if ($_POST) {
            $target = $_POST['target'] ?? '';
            $ports = $_POST['ports'] ?? '';

            if (!$target || !$ports) {
                echo '<div class="card p-6 rounded-xl w-full max-w-2xl text-center mb-8 animate-fadeIn">';
                echo '<p class="text-red-400 font-medium">Erro: IP/Domínio e portas são obrigatórios.</p>';
                echo '</div>';
            } else {
                // Validação e conversão de domínio para IP
                if (filter_var($target, FILTER_VALIDATE_IP)) {
                    $ip = $target;
                } else {
                    $ip = gethostbyname($target);
                    if ($ip === $target) {
                        echo '<div class="card p-6 rounded-xl w-full max-w-2xl text-center mb-8 animate-fadeIn">';
                        echo '<p class="text-red-400 font-medium">Erro: Não foi possível resolver o domínio "' . htmlspecialchars($target) . '".</p>';
                        echo '</div>';
                        $ip = null;
                    }
                }

                if ($ip) {
                    require_once 'functions.php';
                    $portList = array_map('intval', array_filter(array_map('trim', explode(',', $ports)), function($port) {
                        $port = (int)$port;
                        return $port >= 1 && $port <= 65535;
                    }));

                    if (empty($portList)) {
                        echo '<div class="card p-6 rounded-xl w-full max-w-2xl text-center mb-8 animate-fadeIn">';
                        echo '<p class="text-yellow-400 font-medium">Nenhuma porta válida fornecida.</p>';
                        echo '</div>';
                    } else {
                        $results = [];
                        foreach ($portList as $port) {
                            $status = isPortOpen($ip, $port) ? 'aberta' : 'fechada';
                            $results[] = ['port' => $port, 'status' => $status];
                        }

                        echo '<div class="card p-8 rounded-2xl w-full max-w-4xl mb-10 animate-fadeIn">';
                        echo '<h2 class="text-2xl font-bold mb-6 text-center bg-clip-text text-transparent bg-gradient-to-r from-[#0EE57F] to-[#0BA861]">';
                        echo 'Resultados para: <span class="text-white">' . htmlspecialchars($target) . '</span> (' . $ip . ')';
                        echo '</h2>';
                        echo '<div class="overflow-x-auto">';
                        echo '<table class="min-w-full divide-y divide-gray-700">';
                        echo '<thead class="bg-gray-800"><tr>';
                        echo '<th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Porta</th>';
                        echo '<th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Status</th>';
                        echo '</tr></thead>';
                        echo '<tbody class="divide-y divide-gray-700">';

                        foreach ($results as $result) {
                            $colorClass = $result['status'] === 'aberta' ? 'status-open' : 'status-closed';
                            echo '<tr class="hover:bg-gray-800 transition-colors">';
                            echo '<td class="px-6 py-4 whitespace-nowrap text-lg font-mono">' . $result['port'] . '</td>';
                            echo '<td class="px-6 py-4 whitespace-nowrap text-lg font-bold ' . $colorClass . '">' . ucfirst($result['status']) . '</td>';
                            echo '</tr>';
                        }

                        echo '</tbody></table></div></div>';
                    }
                }
            }
        }
        ?>
    </div>

    <footer class="mt-auto pt-10 pb-6 text-center text-gray-500 text-sm w-full">
        <p>&copy; <?php echo date('Y'); ?> Scanner de Portas. Todos os direitos reservados.</p>
    </footer>
</body>
</html>