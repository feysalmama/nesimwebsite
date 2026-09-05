import type { Metadata } from "next";

export default function StructuredData({ data }: { data: Record<string, unknown> }) {
  return (
    <script
      type="application/ld+json"
      dangerouslySetInnerHTML={{ __html: JSON.stringify(data) }}
    />
  );
}

export function organizationSchema(org: {
  name: string;
  url: string;
  logo?: string;
  email?: string;
  phone?: string;
  address?: string;
}) {
  return {
    "@context": "https://schema.org",
    "@type": "NGO",
    name: org.name,
    url: org.url,
    logo: org.logo,
    email: org.email,
    telephone: org.phone,
    address: org.address
      ? { "@type": "PostalAddress", streetAddress: org.address }
      : undefined,
  };
}

export function articleSchema(article: {
  title: string;
  description?: string;
  url: string;
  image?: string;
  datePublished?: string;
  dateModified?: string;
  authorName?: string;
  orgName: string;
  orgLogo?: string;
}) {
  return {
    "@context": "https://schema.org",
    "@type": "Article",
    headline: article.title,
    description: article.description,
    url: article.url,
    image: article.image,
    datePublished: article.datePublished,
    dateModified: article.dateModified || article.datePublished,
    author: article.authorName
      ? { "@type": "Person", name: article.authorName }
      : undefined,
    publisher: {
      "@type": "Organization",
      name: article.orgName,
      logo: article.orgLogo
        ? { "@type": "ImageObject", url: article.orgLogo }
        : undefined,
    },
  };
}
