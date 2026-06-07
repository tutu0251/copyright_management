require('dotenv/config');
const mongoose = require('mongoose');
const { connectDb } = require('../config/db.js');
const { Permission } = require('../models/Permission.js');
const { Role } = require('../models/Role.js');
const { User } = require('../models/User.js');
const { Work } = require('../models/Work.js');
const { Owner } = require('../models/Owner.js');
const { Licensee } = require('../models/Licensee.js');
const { License } = require('../models/License.js');
const { UsageReport } = require('../models/UsageReport.js');
const { InfringementCase } = require('../models/Case.js');
const { PERMISSION_DEFS, ROLE_PRESETS, DEFAULT_ROLES } = require('../config/permissions.js');

const WORK_TITLES = [
  'Aurora Fields — Stock Photo Pack',
  'Midnight Choir — Master Recording',
  'Meridian SaaS — Onboarding Video',
  'Policy Handbook 2026 (Internal)',
  'LedgerFlow — Mobile App UI',
];
const WORK_TYPES = ['Image', 'Audio', 'Video', 'Text', 'Software'];
const CREATORS = ['Jamie Chen', 'Riley Ortiz', 'Sam Okonkwo'];
const OWNERS = ['Studio North LLC', 'Echo Lane Music', 'Pixel Harbor Inc.'];

async function seed() {
  await connectDb();

  for (const def of PERMISSION_DEFS) {
    await Permission.updateOne({ slug: def.slug }, { $setOnInsert: def }, { upsert: true });
  }
  const permBySlug = Object.fromEntries(
    (await Permission.find()).map((p) => [p.slug, p._id]),
  );

  for (const roleDef of DEFAULT_ROLES) {
    const slugs = ROLE_PRESETS[roleDef.slug] || [];
    const permIds = slugs.map((s) => permBySlug[s]).filter(Boolean);
    await Role.updateOne(
      { slug: roleDef.slug },
      { $set: { ...roleDef, permissions: permIds } },
      { upsert: true },
    );
  }

  const adminRole = await Role.findOne({ slug: 'admin' });
  const viewerRole = await Role.findOne({ slug: 'viewer' });

  let admin = await User.findOne({ email: 'admin@example.com' });
  if (!admin) {
    admin = await User.create({
      email: 'admin@example.com',
      displayName: 'System Admin',
      passwordHash: await User.hashPassword('Admin123!'),
      roles: [adminRole._id],
      isActive: true,
    });
    console.log('Admin user created: admin@example.com / Admin123!');
  }

  if ((await Work.countDocuments()) === 0) {
    const works = [];
    for (let i = 0; i < WORK_TITLES.length; i++) {
      works.push(
        await Work.create({
          title: WORK_TITLES[i],
          workType: WORK_TYPES[i % WORK_TYPES.length],
          creator: CREATORS[i % CREATORS.length],
          owner: OWNERS[i % OWNERS.length],
          copyrightStatus: 'registered',
          riskLevel: ['Low', 'Medium', 'High'][i % 3],
          registeredAt: new Date(2025, 8 + (i % 4), 1 + i),
          createdBy: admin._id,
        }),
      );
    }

    const owner = await Owner.create({
      legalName: 'Studio North LLC',
      entityType: 'company',
      email: 'legal@studionorth.example',
    });

    const licensee = await Licensee.create({
      name: 'Brightfield Media',
      contactEmail: 'licensing@brightfield.example',
      organization: 'Brightfield Media Group',
    });

    await License.create({
      work: works[0]._id,
      licensee: licensee._id,
      licenseType: 'non_exclusive',
      licenseStatus: 'active',
      paymentStatus: 'paid',
      feeAmount: 2500,
      startDate: new Date(2025, 0, 1),
      endDate: new Date(2026, 11, 31),
    });

    await UsageReport.create({
      work: works[1]._id,
      usageType: 'suspected',
      detectedSource: 'Social crawl',
      detectedAt: new Date(),
    });

    await InfringementCase.create({
      title: 'Unauthorized stream — Midnight Choir',
      work: works[1]._id,
      caseStatus: 'investigating',
      priority: 'high',
      description: 'Detected on third-party platform.',
    });

    console.log(`Seeded ${works.length} works, sample owner, licensee, license, usage report, and case.`);
  }

  console.log('Seed complete.');
  await mongoose.disconnect();
}

seed().catch((err) => {
  console.error(err);
  process.exit(1);
});
