<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RealHem • Transactional Email Templates Gallery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto space-y-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-blue-500/20">
                    RH
                </div>
                <div>
                    <h1 class="text-xl font-extrabold text-white tracking-tight flex items-center gap-2">
                        RealHem Email Design System
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-400 border border-blue-500/30 uppercase tracking-wider">
                            Live Preview
                        </span>
                    </h1>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Production transactional HTML email templates with cross-client support (Gmail, iOS, Outlook)
                    </p>
                </div>
            </div>
            <div>
                <a href="{{ config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition">
                    &larr; Back to App
                </a>
            </div>
        </div>

        <!-- Templates Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @foreach ($templates as $tmpl)
                <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-5 hover:border-blue-500/50 hover:bg-slate-800 transition group flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-lg bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                {{ $tmpl['category'] }}
                            </span>
                            <span class="text-[11px] text-slate-400">
                                Recipient: <strong class="text-slate-200">{{ $tmpl['recipient'] }}</strong>
                            </span>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white group-hover:text-blue-400 transition">
                                {{ $tmpl['name'] }}
                            </h3>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                                {{ $tmpl['description'] }}
                            </p>
                        </div>
                    </div>

                    <div class="pt-5 mt-5 border-t border-slate-700/60 flex items-center justify-between">
                        <span class="text-[11px] font-mono text-slate-500">
                            /dev/mail-preview/{{ $tmpl['id'] }}
                        </span>
                        <a href="{{ url('/dev/mail-preview/' . $tmpl['id']) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white transition shadow-sm shadow-blue-500/30">
                            Open Preview &rarr;
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Embedded Preview Card -->
        <div class="bg-slate-800/50 border border-slate-700/60 rounded-2xl p-6 text-xs text-slate-400 space-y-2">
            <h4 class="font-bold text-slate-200 text-sm">💡 Testing &amp; Development Highlights</h4>
            <ul class="list-disc pl-5 space-y-1">
                <li><strong class="text-slate-300">Brand Consistency:</strong> All templates automatically inherit RealHem's Royal Blue &amp; Indigo branding, clean typography, verified badges, and company footer.</li>
                <li><strong class="text-slate-300">Markdown Compatibility:</strong> Any standard Laravel <code class="text-blue-400">MailMessage</code> notification rendered anywhere in the app automatically adopts this design.</li>
                <li><strong class="text-slate-300">Responsive &amp; Mobile-Optimized:</strong> Fluid card containers adapt seamlessly to small smartphone screens and full desktop viewports.</li>
            </ul>
        </div>
    </div>
</body>
</html>

