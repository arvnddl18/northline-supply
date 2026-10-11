-- CreateTable
CREATE TABLE "shops" (
    "id" TEXT NOT NULL PRIMARY KEY,
    "shopify_domain" TEXT NOT NULL,
    "access_token" TEXT,
    "stripe_account_id" TEXT,
    "currency" TEXT NOT NULL DEFAULT 'AUD',
    "settings" TEXT NOT NULL DEFAULT '{}',
    "installed_at" DATETIME,
    "uninstalled_at" DATETIME,
    "created_at" DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" DATETIME NOT NULL
);

-- CreateIndex
CREATE UNIQUE INDEX "shops_shopify_domain_key" ON "shops"("shopify_domain");
