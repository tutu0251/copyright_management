<p class="page-intro">Visual-only form for capturing a new work. Submit actions are disabled in this mockup.</p>

<div class="card" style="max-width: 880px;">
    <h2 class="card__title">Work metadata</h2>
    <form class="stack" action="#" method="post" onsubmit="return false;">
        <div class="form-grid">
            <div class="field">
                <label for="title">Title</label>
                <input class="input" id="title" name="title" type="text" placeholder="e.g. Meridian Annual Report 2026">
            </div>
            <div class="field">
                <label for="type">Work type</label>
                <select class="select" id="type" name="type">
                    <option>Image</option>
                    <option>Audio</option>
                    <option>Video</option>
                    <option>Text</option>
                    <option>Software</option>
                    <option>Design</option>
                    <option>Course</option>
                </select>
            </div>
            <div class="field">
                <label for="owner">Rights owner</label>
                <input class="input" id="owner" name="owner" type="text" placeholder="Legal entity name">
            </div>
            <div class="field">
                <label for="date">Registration date</label>
                <input class="input" id="date" name="date" type="date" value="2026-05-03">
            </div>
        </div>
        <div class="field">
            <label for="desc">Description</label>
            <textarea class="textarea" id="desc" name="description" placeholder="Short synopsis or catalog notes…"></textarea>
        </div>
        <div class="field">
            <label for="creators">Creators / authors</label>
            <input class="input" id="creators" name="creators" type="text" placeholder="Comma-separated names">
        </div>
        <div class="field">
            <label>Evidence files</label>
            <input type="file" disabled>
            <span class="muted">File upload is disabled in the mockup.</span>
        </div>
        <div class="toolbar" style="margin: 0; padding-top: 0.5rem;">
            <div class="toolbar__left"></div>
            <div class="toolbar__right">
                <a class="btn btn--secondary" href="<?= site_url('mockup/assets') ?>">Cancel</a>
                <button type="submit" class="btn btn--primary" disabled>Save draft</button>
                <button type="button" class="btn btn--primary" disabled>Submit for review</button>
            </div>
        </div>
    </form>
</div>
