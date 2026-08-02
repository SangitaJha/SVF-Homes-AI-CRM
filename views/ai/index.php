<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                <div>
                    <h3 class="h5 mb-1">AI & Automation Center</h3>
                    <p class="text-muted mb-0">Launch the executive AI cockpit for predictions, alerts, automations, and CRM intelligence.</p>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-accent" href="<?= e(app_url('/ai/automation-dashboard')) ?>">Open Dashboard</a>
                    <a class="btn btn-outline-primary" href="<?= e(app_url('/ai/automation-rules')) ?>">Rules Builder</a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h3 class="h5">AI Chat Assistant</h3>
                <form class="ai-form" data-endpoint="<?= e(app_url('/ai/chat')) ?>">
                    <?= csrf_field() ?>
                    <textarea name="prompt" class="form-control mb-3" rows="4" placeholder="Ask: Show today's follow-ups"></textarea>
                    <button class="btn btn-accent" type="submit">Ask AI</button>
                </form>
                <div class="ai-result mt-3"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h3 class="h5">Lead Scoring</h3>
                <form class="ai-form" data-endpoint="<?= e(app_url('/ai/lead-score')) ?>">
                    <?= csrf_field() ?>
                    <div class="row g-2 mb-3">
                        <div class="col-6"><input type="number" name="budget" class="form-control" placeholder="Budget"></div>
                        <div class="col-6"><input type="number" name="followups" class="form-control" placeholder="Follow-ups"></div>
                        <div class="col-6"><input type="number" name="interest" class="form-control" placeholder="Interest (1-10)"></div>
                        <div class="col-6"><input type="number" name="response_time" class="form-control" placeholder="Response Time (mins)"></div>
                    </div>
                    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="site_visit" value="1" id="siteVisit"><label class="form-check-label" for="siteVisit">Site visit scheduled</label></div>
                    <button class="btn btn-accent" type="submit">Score Lead</button>
                </form>
                <div class="ai-result mt-3"></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100"><div class="card-body"><h3 class="h6">WhatsApp Generator</h3><form class="ai-form" data-endpoint="<?= e(app_url('/ai/whatsapp-message')) ?>"><?= csrf_field() ?><input class="form-control mb-2" name="name" placeholder="Customer name"><input class="form-control mb-2" name="project" placeholder="Project name"><button class="btn btn-outline-primary w-100" type="submit">Generate</button></form><div class="ai-result mt-3"></div></div></div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100"><div class="card-body"><h3 class="h6">Email Generator</h3><form class="ai-form" data-endpoint="<?= e(app_url('/ai/email-message')) ?>"><?= csrf_field() ?><input class="form-control mb-2" name="name" placeholder="Customer name"><input class="form-control mb-2" name="project" placeholder="Project name"><button class="btn btn-outline-primary w-100" type="submit">Generate</button></form><div class="ai-result mt-3"></div></div></div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100"><div class="card-body"><h3 class="h6">SOP Generator</h3><form class="ai-form" data-endpoint="<?= e(app_url('/ai/sop-generator')) ?>"><?= csrf_field() ?><input class="form-control mb-2" name="topic" placeholder="e.g. site handover"><button class="btn btn-outline-primary w-100" type="submit">Generate SOP</button></form><div class="ai-result mt-3"></div></div></div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100"><div class="card-body"><h3 class="h6">Document Reader</h3><form class="ai-form" data-endpoint="<?= e(app_url('/ai/document-reader')) ?>" enctype="multipart/form-data"><?= csrf_field() ?><input type="file" name="document" class="form-control mb-2"><button class="btn btn-outline-primary w-100" type="submit">Process</button></form><div class="ai-result mt-3"></div></div></div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100"><div class="card-body"><h3 class="h6">Voice Commands</h3><form class="ai-form" data-endpoint="<?= e(app_url('/ai/voice-command')) ?>"><?= csrf_field() ?><input class="form-control mb-2" name="command" placeholder="Add lead"><button class="btn btn-outline-primary w-100" type="submit">Run</button></form><div class="ai-result mt-3"></div></div></div>
    </div>
</div>